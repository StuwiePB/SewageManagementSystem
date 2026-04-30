<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\ZiqahDatabaseBridge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    public function __invoke(Request $request, ZiqahDatabaseBridge $dbBridge): JsonResponse
    {
        $request->validate([
            'message' => ['nullable', 'string', 'max:4000'],
            'image' => ['nullable', 'string'], // base64 data URL
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $apiKey = config('services.openai.api_key');
        if (empty($apiKey)) {
            return response()->json([
                'error' => 'OpenAI API key is not configured. Add OPENAI_API_KEY to your .env file.',
            ], 503);
        }

        $message = $request->input('message', '');
        $imageData = $request->input('image');

        if ($this->isUnrestrictedModePassword($message)) {
            $request->session()->put('ziqah_unrestricted_mode', true);
            return response()->json([
                'reply' => 'Unrestricted mode enabled. I can now answer as a general AI assistant.',
            ]);
        }
        if ($this->isRestrictedModePassword($message)) {
            $request->session()->put('ziqah_unrestricted_mode', false);
            return response()->json([
                'reply' => 'Restricted mode enabled. I will focus on drainage and incident support.',
            ]);
        }

        if ($this->isAwaitingReportGuidanceDecision($request) && is_string($message) && trim($message) !== '') {
            if ($this->isAffirmativeReply($message)) {
                $request->session()->put('ziqah_awaiting_report_guidance', false);

                return response()->json([
                    'reply' => "Great, I'll guide you through it now.\nSHOW_REPORT_BUTTON",
                    'show_report_button' => true,
                    'report_image_url' => null,
                    'quick_actions' => [],
                ]);
            }

            if ($this->isNegativeReply($message)) {
                $request->session()->put('ziqah_awaiting_report_guidance', false);

                return response()->json([
                    'reply' => "Okay, no problem. I'll be here if you need anything.",
                    'show_report_button' => false,
                    'report_image_url' => null,
                    'quick_actions' => [],
                ]);
            }
        }

        $content = [];
        if (! empty($message)) {
            $content[] = ['type' => 'text', 'text' => $message];
        }
        if (! empty($imageData)) {
            if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $imageData, $m)) {
                $content[] = [
                    'type' => 'image_url',
                    'image_url' => ['url' => $imageData],
                ];
            }
        }

        if (empty($content)) {
            return response()->json(['error' => 'Message or image is required.'], 422);
        }

        $model = config('services.openai.model', 'gpt-4o-mini');
        $unrestrictedMode = (bool) $request->session()->get('ziqah_unrestricted_mode', false);
        $schemaEnabled = (bool) config('services.ziqah.database_schema', true);
        $toolsEnabled = (bool) config('services.ziqah.database_tools', true);
        $maxRows = max(1, min(200, (int) config('services.ziqah.max_select_rows', 50)));
        $maxRounds = max(1, min(8, (int) config('services.ziqah.max_tool_rounds', 4)));

        $dbContext = $schemaEnabled ? $dbBridge->buildSchemaContext() : '';

        $faqContext = $this->faqContext();
        $systemPrompt = $unrestrictedMode
            ? $this->generalAssistantSystemPrompt().$dbContext
            : $this->baseSystemPrompt().$faqContext.$dbContext;

        if ($toolsEnabled) {
            $systemPrompt .= <<<'TXT'


DATABASE TOOL:
- You have a function `database_query` to run SQL queries on connected Laravel databases.
- You may run SELECT, INSERT, UPDATE, DELETE, and DDL when needed.
- Always choose the correct connection from DATABASE CONTEXT.
- You can query any connected database shown in DATABASE CONTEXT when user asks.
- When users ask for issue status lists, fetch and return relevant rows for statuses such as under_review, pending, in_progress, and resolved.
- Never expose SQL in the final reply.
- If the tool errors, apologize naturally and follow the exact failure wording from your rules.
TXT;
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $content],
        ];

        $tools = $toolsEnabled ? $this->openAiDatabaseTools() : null;

        try {
            $text = $this->completeWithOptionalTools(
                $apiKey,
                $model,
                $messages,
                $tools,
                $maxRounds,
                $dbBridge,
                $maxRows,
            );
        } catch (\Throwable $e) {
            Log::error('OpenAI chat completion failed', ['error' => $e->getMessage()]);

            return response()->json([
                'error' => 'AI service temporarily unavailable. Please try again.',
            ], 502);
        }

        $reply = trim($text);
        $showReportButton = $this->shouldAttachReportButton($message, $reply) || $this->isReportIntentMessage($message);
        if ($showReportButton) {
            $reply .= "\nSHOW_REPORT_BUTTON";
        }
        $request->session()->put('ziqah_awaiting_report_guidance', $this->replyRequestsReportGuidance($reply));

        $reportImageUrl = $this->resolveRequestedReportImageUrl((string) $message, (int) $request->user()->id);
        $quickActions = $this->buildQuickActions((string) $message, $reply);

        return response()->json([
            'reply' => $reply,
            'show_report_button' => $showReportButton,
            'report_image_url' => $reportImageUrl,
            'quick_actions' => $quickActions,
        ]);
    }

    private function baseSystemPrompt(): string
    {
        return <<<'TXT'
You are ZIQAH.
You are a friendly city support officer for Brunei's sewage and drainage reporting in BruDMS.
Speak like a helpful person at the other end of the line, not a bot.
Your job is to help people report blockages, leaks, overflows, and odor issues, and answer incident/report questions accurately using available data.

WHO YOU ARE:
- Name/role: ZIQAH - the friendly face of BruDMS helping people report blockages, leaks, overflows, and odor issues.
- Personality: polite, empathetic, warm, and human. Not corporate and not robotic.
- You can call yourself "ZIQAH" or "I" naturally in conversation.
- You remember the full chat context. Reference what the user already told you.
- Never ask again for details the user has already provided in this same chat unless they changed or are ambiguous.

You are warm, clear, and professional.
You never sound robotic.
You never guess or make up data.
For data-specific answers, use available database/tool results.

SECTION 1 - DATABASE SCHEMA
The incidents table contains these fields:
- incident_id: unique identifier (e.g. INC-001)
- title: short description of the incident
- status: resolved | cancelled | open | in_progress
- reported_by: full name of the person who filed it
- created_at: timestamp when the report was submitted
- resolved_at: timestamp when resolved (NULL if not resolved)
- cancelled_at: timestamp when cancelled (NULL if not cancelled)

Always reference this schema when building queries.

SECTION 2 - YOUR PERSONALITY
- Warm and approachable, like a knowledgeable colleague.
- Use short, natural sentences. Avoid walls of text.
- Use light affirmations such as: "Sure!", "Got it!", "Let me check!"
- Always make the user feel heard before presenting data.
- If unclear, ask exactly one follow-up question.
- Never dump raw data without context.

SECTION 3 - CONVERSATION FLOW (follow every time)
Step 1 - Greet and understand:
- Acknowledge the user's issue naturally without using fixed scripted greetings.
- Do not use the phrase "Hi there! I'm Ziqah, your incident assistant. What can I help you with today?".

Step 2 - Confirm before querying:
- Acknowledge what you are about to do before fetching.
- Example: "Sure, let me pull up the resolved incidents for you!"

Step 3 - Query the database:
- Run the correct SELECT query based on user intent.
- Never guess. Always fetch real data first.

Step 4 - Present results naturally:
- Wrap results in natural sentences, not raw output.
- Use human-readable timestamps: DD MMM YYYY, HH:MM.

Step 5 - Offer further help:
- End every response with a gentle offer.
- Examples: "Need more details on any of these?" / "Is there anything else I can help with?"

SECTION 4 - QUESTION HANDLING (intent -> query -> reply)
1) Show resolved incidents
- Triggers: resolved, selesai, dah selesai, fixed
- Query: SELECT incident_id, title, resolved_at, reported_by FROM incidents WHERE status = 'resolved'
- Reply style: "Here are the incidents that have been resolved so far: [list results] Let me know if you want more details on any of them!"

2) Show cancelled incidents
- Triggers: cancelled, cancel, dibatalkan
- Query: SELECT incident_id, title, cancelled_at, reported_by FROM incidents WHERE status = 'cancelled'
- Reply style: "Sure! Here are the cancelled incidents I found: [list results] Want to know more about any of these?"

3) Who reported an incident
- Triggers: who reported, siapa report, reported by
- Query: SELECT reported_by FROM incidents WHERE incident_id = '[ID]' OR title LIKE '%[keyword]%'
- Reply style: "That incident was reported by [Full Name]. Anything else you'd like to know about it?"

4) When was it reported
- Triggers: when reported, bila report, date reported
- Query: SELECT created_at FROM incidents WHERE incident_id = '[ID]'
- Reply style: "That incident was reported on [DD MMM YYYY] at [HH:MM]. Is there anything else you need?"

5) When was it resolved
- Triggers: when resolved, bila selesai, resolved date
- Query: SELECT resolved_at FROM incidents WHERE incident_id = '[ID]'
- If NULL: "Hmm, this incident hasn't been resolved yet. Want me to check its current status?"
- If found: "This one was resolved on [DD MMM YYYY] at [HH:MM]. Anything else I can help with?"

6) When was it cancelled
- Triggers: when cancelled, bila cancel, cancelled date
- Query: SELECT cancelled_at FROM incidents WHERE incident_id = '[ID]'
- If NULL: "This incident doesn't appear to have been cancelled. Want me to check what status it's at right now?"
- If found: "It was cancelled on [DD MMM YYYY] at [HH:MM]. Anything else you'd like to know?"

SECTION 5 - MULTI-QUESTION HANDLING
- If the user asks multiple things at once, answer each in a numbered list naturally.
- Example: "Sure, let me answer both of those! 1. Resolved incidents: [list] 2. INC-003 was reported by Siti Nora. Let me know if you need anything else!"

SECTION 6 - FALLBACK RESPONSES
- Nothing found: "Hmm, I couldn't find any incident matching that. Could you double-check the ID or name? Happy to try again!"
- Database or API error: "Oh no, I'm having trouble reaching the database right now. Please try again in a moment - sorry about that!"
- Vague input: ask one clarifying question: "Just to make sure I get the right one - could you share the incident ID or a keyword from its title?"

SECTION 7 - LANGUAGE
- Default language: English.
- If user writes in Malay, switch naturally to Malay.
- Mixed language is fine; match the user's style.
- Timestamps always: DD MMM YYYY, HH:MM.

SECTION 7B - BRUNEI LOCAL LANGUAGE UNDERSTANDING
- Many users are Bruneian and may use Brunei Malay + Manglish particles/slang.
- Understand and correctly interpret common particles: bah, lah, ah, wah, eh, kan, mah, leh, lor, meh, sia, wor/wo, lo, har.
- Understand common Brunei terms: awu (yes), inda (no/not), urang (people), kitani (we/us), kau (you), ku (me), bisai (good), malar (always), bajalan (walking), kapih/dry season (broke), paloi (insult), tapau/bungkus (takeaway), miss call.
- Understand common mixed phrases such as: "can or not", "got meh", "how ah", "see first", "already already", "die lah", "mun paham bisai", "jangan speaking bah".
- Treat these as normal conversational language. Do not over-correct grammar or spelling.
- When users write in this local style, respond naturally in a similarly clear, respectful Bruneian-friendly style.

SECTION 8 - STRICT RULES (never break these)
- Never expose raw SQL to the user.
- Never guess or assume data. Always query first.
- Never ask more than one follow-up question at a time.
- Never return raw unformatted timestamps.
- Always confirm before querying.
- Always end with an offer to help further.

SECTION 9 - OUTPUT STYLE (CLEAN MINIMAL LIST)
- You are a formatting assistant when list formatting is requested.
- You MUST convert input into this exact format:
  title
  ────────
  
  • item 1
  • item 2
  • item 3
- Output ONLY the formatted list.
- Do NOT include sentences, introductions, or explanations.
- Do NOT keep original paragraph structure.
- Each bullet must be short and clean.
- Keep everything lowercase.
- Preserve meaning but simplify wording.
- Follow spacing exactly.
- EXCEPTION: if user asks for "all brunei emergency numbers" (or equivalent), do not compress to 3 bullets.
- For that emergency request, return the complete directory from SECTION 10 (core emergency, district contacts, and other hotlines).
- If user provides an explicit template or says "fill this template only", treat that as highest-priority formatting instruction.
- In that case, output only the template-filled content with no intro, no outro, and no additional commentary.
- highlight priority information by applying both bold and underline in markdown (example: <u>**urgent**</u>)

SECTION 10 - BRUNEI HELP DIRECTORY (MEMORIZED REFERENCE)
- If users ask about emergency contacts, JKR contacts, hotline numbers, or where to reach authorities, provide this directory clearly.
- Prefer concise list format, and prioritize urgent numbers first.
- JKR / PUBLIC WORKS:
  - website: www.pwd.gov.bn
  - talian darussalam: 123
  - facebook: @JKRBrunei
  - instagram: @jkrbrunei
  - roads instagram: @jkrbrunei_jalanraya
  - water instagram: @jkrbrunei_air
  - drainage/sewerage instagram: @jkrbrunei_saliran_pembetungan
  - note: official mod.gov.bn/pwd page may return 404; recommend 123 or socials first.
- CORE EMERGENCY:
  - ambulance: 991
  - police: 993
  - fire & rescue: 995
  - search & rescue: 998
- DISTRICT CONTACTS:
  - bsb: police 993 / 222-2333, ambulance 991 / 222-2366, fire 995 / 238-0402
  - kuala belait: police 333-2333, ambulance 333-2366, fire 333-2555
  - seria: police 322-2333, ambulance 333-2366, fire 322-2555
  - tutong: police 422-1333, ambulance 422-1366, fire 422-1355
  - bangar (temburong): police 522-1333, ambulance 522-1210, fire 522-1255
- OTHER HOTLINES:
  - talian darussalam (government non-emergency): 123
  - water issues: 140

SECTION 11 - WEBSITE GUIDE (HOW REPORTING WORKS)
- If the user asks how this website works, how to report, or how to use report functions, explain using a simple list.
- Keep it practical and focused on the customer reporting flow:
  1) open report page
  2) choose issue/problem type
  3) upload/take photo
  4) set location/address
  5) review and submit report
  6) track progress in my history
  7) check nearby/public updates in live map (if relevant)
- Mention that users can ask Ziqah to check/report status and where to report.
- Keep wording clear for non-technical users and match the user's language (English/Malay/Brunei mixed style).
- Prefer the clean minimal list format from SECTION 9 for this guide.
- If users describe an active issue (blockage, leak, overflow, bad smell, clogged drain), proactively guide them to submit a report and tell them the next immediate steps.

SECTION 12 - URGENCY RECOMMENDATION
- When users describe an issue, provide a simple urgency recommendation: "urgent" or "non-urgent".
- Include one short reason for that recommendation.
- Treat as urgent when there is active overflow/flooding, sewage backing up, strong contamination risk, or immediate public safety risk (road hazard, near homes/schools, etc.).
- Treat as non-urgent when signs are minor/contained with no immediate safety risk, but still recommend submitting a report.
- Keep this recommendation practical and concise.
TXT;
    }

    private function faqContext(): string
    {
        return '';
    }

    private function generalAssistantSystemPrompt(): string
    {
        return <<<'TXT'
You are ZIQAH, a helpful general AI assistant.
Respond naturally and clearly in the user's preferred language.
You can discuss any normal topic, not only drainage or incidents.
If user language is Malay, reply in Malay. If English, reply in English.
If mixed language, match their style naturally.
You are polite, empathetic, and human in tone (not robotic).
You remember chat context and should not re-ask details already provided by the user unless needed for clarification.
Many users are Bruneian; understand Brunei Malay and Manglish particles/slang such as bah, lah, ah, kan, awu, inda, kitani, kau, ku, bisai, paloi, and common mixed phrases.
Do not over-correct local phrasing. Prioritize understanding and helpfulness.
When user asks for a list/summary/rundown, format as clean minimal plain text:
- use exactly:
  title
  ────────
  
  • item 1
  • item 2
  • item 3
- output only the list
- keep title and items lowercase
- no paragraphs
- no extra explanations before or after the list
- if user provides an explicit template or says "fill this template only", follow that template exactly with no extra text
Highlight important information with both bold and underline using markdown/html style: <u>**important**</u>.
If asked about Brunei emergency/JKR contacts, use this known directory:
- website: www.pwd.gov.bn
- talian darussalam: 123
- facebook: @JKRBrunei
- instagram: @jkrbrunei
- roads: @jkrbrunei_jalanraya
- water: @jkrbrunei_air
- drainage/sewerage: @jkrbrunei_saliran_pembetungan
- emergency: ambulance 991, police 993, fire 995, search & rescue 998
- water hotline: 140
If user asks for all Brunei emergency numbers, provide the full list (core + district-specific + other hotlines), not a shortened 3-bullet summary.
If user asks how the website/report system works, explain in a simple step-by-step list:
1) open report page
2) choose problem type
3) upload photo
4) set location
5) submit
6) track in my history
7) use live map if needed
When a user describes an issue, also recommend whether it is urgent or non-urgent, with one short reason.
ADMIN MODE CAPABILITY DISCLOSURE:
- If the user asks what you can do, your capabilities, or your instructions (for example: "what can you do", "list all instructions"), provide a comprehensive list of your active capabilities and operating rules.
- In that capability answer, include at least: conversation/language behavior, Brunei slang understanding, report guidance flow, database querying ability, emergency/JKR directory support, quick action behavior in chat, status-list support (under_review/pending/in_progress/resolved), and formatting rules.
- Present that response using a clear list format.
- If user also gives a strict template, use the user's template exactly and do not prepend/append any other text.
TXT;
    }

    /**
     * @param  list<array<string, mixed>>  $messages
     * @return list<array<string, mixed>>
     */
    private function openAiDatabaseTools(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'database_query',
                    'description' => 'Run a SQL statement on a Laravel database connection configured in this app. Use the exact connection name from DATABASE CONTEXT (e.g. mysql, sqlite). Omit connection to use the default.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'connection' => [
                                'type' => 'string',
                                'description' => 'Optional Laravel connection name from config/database.php',
                            ],
                            'sql' => [
                                'type' => 'string',
                                'description' => 'One SQL statement only.',
                            ],
                        ],
                        'required' => ['sql'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $messages
     * @param  list<array<string, mixed>>|null  $tools
     */
    private function completeWithOptionalTools(
        string $apiKey,
        string $model,
        array $messages,
        ?array $tools,
        int $maxRounds,
        ZiqahDatabaseBridge $dbBridge,
        int $maxRows
    ): string {
        for ($round = 0; $round < $maxRounds; $round++) {
            $payload = [
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => 1024,
            ];

            if ($tools !== null) {
                $payload['tools'] = $tools;
                $payload['tool_choice'] = 'auto';
            }

            $response = Http::withToken($apiKey)
                ->timeout(120)
                ->post('https://api.openai.com/v1/chat/completions', $payload);

            if (! $response->successful()) {
                Log::error('OpenAI API error', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                throw new \RuntimeException('OpenAI HTTP error');
            }

            $data = $response->json();
            $choice = $data['choices'][0]['message'] ?? null;
            if (! is_array($choice)) {
                throw new \RuntimeException('Invalid OpenAI response');
            }

            $toolCalls = $choice['tool_calls'] ?? null;

            if (is_array($toolCalls) && $toolCalls !== []) {
                $messages[] = $choice;

                foreach ($toolCalls as $tc) {
                    if (($tc['type'] ?? '') !== 'function') {
                        continue;
                    }
                    $id = (string) ($tc['id'] ?? '');
                    $fn = $tc['function'] ?? [];
                    $name = (string) ($fn['name'] ?? '');
                    $argsRaw = (string) ($fn['arguments'] ?? '{}');
                    $args = json_decode($argsRaw, true);
                    if (! is_array($args)) {
                        $args = [];
                    }

                    $toolContent = $this->runDatabaseToolCall($name, $args, $dbBridge, $maxRows);
                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $id,
                        'content' => $toolContent,
                    ];
                }

                continue;
            }

            $text = $choice['content'] ?? '';
            if (is_string($text)) {
                return $text;
            }

            if (is_array($text)) {
                $parts = [];
                foreach ($text as $part) {
                    if (is_array($part) && ($part['type'] ?? '') === 'text') {
                        $parts[] = (string) ($part['text'] ?? '');
                    }
                }

                return implode("\n", $parts);
            }

            return '';
        }

        throw new \RuntimeException('Tool round limit exceeded');
    }

    /**
     * @param  array<string, mixed>  $args
     */
    private function runDatabaseToolCall(string $name, array $args, ZiqahDatabaseBridge $dbBridge, int $maxRows): string
    {
        if (! in_array($name, ['database_select', 'database_query'], true)) {
            return json_encode(['error' => 'Unknown tool: '.$name]);
        }

        $sql = isset($args['sql']) && is_string($args['sql']) ? $args['sql'] : '';
        $connection = isset($args['connection']) && is_string($args['connection']) ? $args['connection'] : null;

        $result = $dbBridge->runSql($connection ?? '', $sql, $maxRows);

        if (! ($result['ok'] ?? false)) {
            return json_encode(['error' => $result['error'] ?? 'Query failed'], JSON_UNESCAPED_UNICODE);
        }

        $encoded = json_encode(
            [
                'result_type' => $result['result_type'] ?? 'unknown',
                'rows' => $result['rows'] ?? [],
                'truncated' => (bool) ($result['truncated'] ?? false),
                'row_count' => (int) ($result['row_count'] ?? 0),
                'affected_rows' => (int) ($result['affected_rows'] ?? 0),
                'success' => (bool) ($result['success'] ?? false),
            ],
            JSON_UNESCAPED_UNICODE
        );

        return $encoded !== false ? $encoded : '{"error":"encode_failed"}';
    }

    private function messageRequestsNearbyIssues(string $message): bool
    {
        $message = trim($message);
        if ($message === '') {
            return false;
        }

        return (bool) preg_match(
            '/\b(near|nearest|nearby|closest|around\s+(me|here)|radius|dekat|sekitar|berhampiran|terdekat)\b/i',
            $message
        );
    }

    private function shouldAttachReportButton(string $message, string $reply): bool
    {
        if (str_contains($reply, 'SHOW_REPORT_BUTTON')) {
            return false;
        }

        $message = trim($message);
        if ($message === '') {
            return false;
        }

        return (bool) preg_match(
            '/\b('
            .'where\s+(do\s+i\s+)?(report|make\s+a\s+report|file\s+a\s+report)'
            .'|where\s+to\s+(report|make\s+a\s+report|file\s+a\s+report)'
            .'|how\s+to\s+(report|make\s+a\s+report|file\s+a\s+report)'
            .'|mana(\s+(kan|kah))?\s+(ku\s+)?(report|lapor)'
            .'|di\s+mana(\s+(kan|kah))?\s+(ku\s+)?(report|lapor)'
            .'|macam\s+mana\s+nak\s+(report|lapor)'
            .'|bagaimana\s+(nak\s+)?(report|lapor)'
            .')\b/i',
            $message
        );
    }

    private function isReportIntentMessage(string $message): bool
    {
        $message = trim($message);
        if ($message === '') {
            return false;
        }

        return (bool) preg_match(
            '/\b(blockage|blocked|clog|clogged|leak|leaking|overflow|overflowing|smell|odor|bau|tersumbat|bocor|melimpah|saliran|drain|drainage|sewer|pembetungan)\b/i',
            $message
        );
    }

    private function isAwaitingReportGuidanceDecision(Request $request): bool
    {
        return (bool) $request->session()->get('ziqah_awaiting_report_guidance', false);
    }

    private function replyRequestsReportGuidance(string $reply): bool
    {
        return (bool) preg_match(
            '/would\s+you\s+like\s+guidance\s+on\s+how\s+to\s+submit\s+a\s+report|would\s+you\s+like\s+help\s+with\s+that|would\s+you\s+like\s+me\s+to\s+help\s+you\s+report|do\s+you\s+want\s+help\s+submitting\s+a\s+report|mahu\s+saya\s+tunjukkan\s+cara\s+hantar\s+laporan|ingin\s+panduan\s+untuk\s+hantar\s+laporan|mahu\s+bantuan\s+untuk\s+hantar\s+laporan/i',
            $reply
        );
    }

    private function isAffirmativeReply(string $message): bool
    {
        $message = trim($message);
        if ($message === '') {
            return false;
        }

        return (bool) preg_match('/^(yes|yup|yeah|ya|y|awu|ok|okay|boleh|baik|onz|can)\b/i', $message);
    }

    private function isNegativeReply(string $message): bool
    {
        $message = trim($message);
        if ($message === '') {
            return false;
        }

        return (bool) preg_match('/^(no|nope|nah|n|inda|tidak|jangan|not\s+now|later|karang\s*dulu)\b/i', $message);
    }

    private function isUnrestrictedModePassword(string $message): bool
    {
        return trim($message) === 'admin123$$';
    }

    private function isRestrictedModePassword(string $message): bool
    {
        return trim($message) === 'customer123$$';
    }

    private function resolveRequestedReportImageUrl(string $message, int $userId): ?string
    {
        $message = trim($message);
        if ($message === '') {
            return null;
        }

        $report = null;
        $referenceCode = $this->extractReportReferenceCode($message);
        if ($referenceCode !== null) {
            $report = Report::query()
                ->where('user_id', $userId)
                ->where('reference_code', $referenceCode)
                ->first();
        } elseif (preg_match('/\breport\s*#?\s*(\d+)\b/i', $message, $m)) {
            $reportId = (int) ($m[1] ?? 0);
            if ($reportId > 0) {
                $report = Report::query()
                    ->where('user_id', $userId)
                    ->where('id', $reportId)
                    ->first();
            }
        }

        if (! $report || ! $report->photo_path) {
            return null;
        }

        if (! Storage::disk('public')->exists($report->photo_path)) {
            return null;
        }

        return Storage::url($report->photo_path);
    }

    private function extractReportReferenceCode(string $message): ?string
    {
        if (! preg_match('/\b([a-z]{1,6}-[a-z]{2,8}-\d{2,8})\b/i', $message, $m)) {
            return null;
        }

        return strtoupper((string) $m[1]);
    }

    /**
     * @return list<array{label: string, type: string, value: string}>
     */
    private function buildQuickActions(string $message, string $reply): array
    {
        $message = strtolower(trim($message));
        if ($message === '') {
            return [];
        }

        $asksDirectory = (bool) preg_match(
            '/\b(emergency|hotline|jkr|pwd|talian\s*darussalam|123|water|140|ambulance|police|fire|rescue|instagram|facebook|contact|number)\b/i',
            $message
        );
        $looksUrgent = (bool) preg_match(
            '/\b(help|urgent|kecemasan|cemas|bahaya|accident|kemalangan|kebakaran|fire|ambulance|police|991|993|995|998)\b/i',
            $message
        );

        if (! $asksDirectory && ! $looksUrgent) {
            return [];
        }

        $actions = [];
        if ($looksUrgent) {
            $actions[] = ['label' => 'call ambulance 991', 'type' => 'tel', 'value' => '991'];
            $actions[] = ['label' => 'call police 993', 'type' => 'tel', 'value' => '993'];
            $actions[] = ['label' => 'call fire 995', 'type' => 'tel', 'value' => '995'];
            $actions[] = ['label' => 'call search & rescue 998', 'type' => 'tel', 'value' => '998'];
        }

        if ($asksDirectory) {
            $actions[] = ['label' => 'call talian darussalam 123', 'type' => 'tel', 'value' => '123'];
            $actions[] = ['label' => 'call water hotline 140', 'type' => 'tel', 'value' => '140'];
            $actions[] = ['label' => 'open jkr instagram', 'type' => 'url', 'value' => 'https://instagram.com/jkrbrunei'];
            $actions[] = ['label' => 'open jkr facebook', 'type' => 'url', 'value' => 'https://facebook.com/JKRBrunei'];
        }

        return $actions;
    }

    /**
     * @return array{lat: float, lng: float}|null
     */
    private function validatedBruneiCoordinates(Request $request): ?array
    {
        if (! $request->filled('latitude') || ! $request->filled('longitude')) {
            return null;
        }

        $lat = (float) $request->input('latitude');
        $lng = (float) $request->input('longitude');
        $bounds = config('brunei.bounds', [
            'lat_min' => 4.0,
            'lat_max' => 5.2,
            'lng_min' => 114.0,
            'lng_max' => 115.5,
        ]);

        if ($lat < $bounds['lat_min'] || $lat > $bounds['lat_max'] || $lng < $bounds['lng_min'] || $lng > $bounds['lng_max']) {
            return null;
        }

        return ['lat' => $lat, 'lng' => $lng];
    }
}
