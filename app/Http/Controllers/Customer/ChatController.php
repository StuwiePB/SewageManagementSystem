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

        $rememberedArea = (string) $request->session()->get('ziqah_last_area', '');
        $detectedArea = $this->extractBruneiAreaFromMessage($message);
        if ($detectedArea !== null) {
            $request->session()->put('ziqah_last_area', $detectedArea);
            $rememberedArea = $detectedArea;
        }

        $coords = $this->validatedBruneiCoordinates($request);
        if ($coords !== null) {
            $request->session()->put('ziqah_last_lat', $coords['lat']);
            $request->session()->put('ziqah_last_lng', $coords['lng']);
        }

        if ($this->messageRequestsNearbyIssues($message)) {
            $lat = $coords['lat'] ?? $request->session()->get('ziqah_last_lat');
            $lng = $coords['lng'] ?? $request->session()->get('ziqah_last_lng');

            if ($lat !== null && $lng !== null) {
                $nearbyReply = $this->buildNearbyIssuesReply((float) $lat, (float) $lng, $dbBridge, 8, 10.0);
                if ($nearbyReply !== null) {
                    $request->session()->put('ziqah_last_topic', 'issues');

                    return response()->json([
                        'reply' => $nearbyReply,
                        'show_report_button' => false,
                        'report_image_url' => null,
                        'quick_actions' => [],
                    ]);
                }
            }

            return response()->json([
                'reply' => 'i need your location to check this. please allow location access in your browser, or tell me the area (e.g. jerudong, berakas).',
                'show_report_button' => false,
                'report_image_url' => null,
                'quick_actions' => [],
            ]);
        }

        $lastTopic = (string) $request->session()->get('ziqah_last_topic', '');

        if ($this->isCountingQuestion($message) && ! $this->mentionsDatabaseStructure($message)) {
            $referencesIssues = $this->isGisIssueLookupRequest($message);
            $isBareFollowUp = $this->isBareCountFollowUp($message);

            if ($referencesIssues || ($isBareFollowUp && $lastTopic === 'issues')) {
                $countArea = $detectedArea ?? ($rememberedArea !== '' ? $rememberedArea : null);
                if ($countArea !== null && $countArea !== '') {
                    $countReply = $this->buildAreaIssueCountReply($countArea, $dbBridge);
                    if ($countReply !== null) {
                        $request->session()->put('ziqah_last_topic', 'issues');

                        return response()->json([
                            'reply' => $countReply,
                            'show_report_button' => false,
                            'report_image_url' => null,
                            'quick_actions' => [],
                        ]);
                    }
                }
            }
        }

        if ($this->isGisIssueLookupRequest($message)
            && ! $this->isCountingQuestion($message)
            && ! $this->mentionsDatabaseStructure($message)
            && ($detectedArea !== null || $this->messageRequestsNearbyIssues($message))
        ) {
            $lookupArea = $detectedArea ?? $rememberedArea;
            $excludedArea = $this->extractExcludedAreaFromMessage($message, $rememberedArea);
            if ($lookupArea !== '') {
                $gisReply = $this->buildGisIssueDetailsReply($lookupArea, $message, $dbBridge, 20, $excludedArea);
                if ($gisReply !== null) {
                    $request->session()->put('ziqah_last_topic', 'issues');

                    return response()->json([
                        'reply' => $gisReply,
                        'show_report_button' => false,
                        'report_image_url' => null,
                        'quick_actions' => [],
                    ]);
                }
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
        $defaultRounds = max(1, min(8, (int) config('services.ziqah.max_tool_rounds', 4)));
        $maxRounds = $unrestrictedMode ? max($defaultRounds, 6) : $defaultRounds;
        $maxTokens = $unrestrictedMode ? 2048 : 1024;

        $dbContext = $schemaEnabled ? $dbBridge->buildSchemaContext() : '';

        $faqContext = $this->faqContext();
        $systemPrompt = $unrestrictedMode
            ? $this->generalAssistantSystemPrompt().$dbContext."\n\n".$this->buildAppContext()
            : $this->baseSystemPrompt().$faqContext.$dbContext;

        if ($toolsEnabled) {
            $systemPrompt .= <<<'TXT'


DATABASE TOOL:
- You have a function `database_select` to run read-only SELECT queries on connected Laravel databases.
- Always choose the correct connection from DATABASE CONTEXT.
- You can query any connected database shown in DATABASE CONTEXT when user asks.
- For ANY question about data — counts, lists, status, recency, area, reporter, "how many", "show me", "any issues" — you MUST call `database_select` first and base your reply on the result. Never answer from general knowledge.
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
                $maxTokens,
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
You are ZIQAH in UNRESTRICTED / ADMIN mode.
You are a senior, polite, helpful full-stack engineering + product assistant operating inside the BruDMS Laravel app.
You have full read-only access to the project's database schema, project context, and routes (provided below in DATABASE CONTEXT and PROJECT CONTEXT).
You can run read-only SELECT queries via the `database_select` tool when the user asks about data.

LANGUAGE:
- Reply in the user's language. Malay -> Malay. English -> English. Mixed -> match naturally.
- Understand Brunei Malay and Manglish particles/slang (bah, lah, ah, kan, awu, inda, kitani, kau, ku, bisai, paloi, etc). Do not over-correct local phrasing.

CORE OPERATING TRAITS (always apply):
1. Context aware — Reuse the project stack, DB schema, and conversation history. Never ask the user to re-explain their setup if it is already in context.
2. Runnable code only — Provide real, copy-paste-ready snippets. No pseudocode. Always label the file path on the line above the code block (e.g. `// app/Http/Controllers/Customer/ChatController.php`).
3. Explains the why — Add a short "why" note for non-trivial changes so the user learns while shipping. Keep it tight, not lecture-y.
4. Catches bugs early — Proactively flag off-by-one errors, missing deps, null/undefined risks, race conditions, unhandled exceptions, and N+1 queries before the user has to ask.
5. Stack agnostic — You know React, Vue, Next, Svelte, Laravel, Express, FastAPI, etc. When a choice exists, recommend the best fit for THIS project (Laravel + Blade + vanilla JS) and say why.
6. Full stack coverage — Frontend, backend, DB, deployment, infra. Don't tunnel-vision on one layer.
7. No gaslighting — If you don't know something, say so. Never invent npm/composer packages, function signatures, or APIs. If unsure about a Laravel/PHP API, say "I'm not 100% sure — verify in docs" instead of bluffing.
8. Iterates fast — Take feedback like "add dark mode" or "use Pinia instead" and apply it surgically without breaking existing behavior. Show only the changed parts unless the user asks for the full file.
9. Security aware — Flag XSS, SQL injection, CSRF, mass-assignment, exposed secrets in `.env`, hardcoded credentials, missing auth/role checks, and unsafe file uploads. Mention them inline as a "security note" when relevant.
10. Clean output — Use proper markdown: `##` / `###` headings when sections help, fenced code blocks with the language tag (```php, ```js, ```sql, ```bash), inline `code` for symbols, file-path labels above blocks. No wall-of-text.

OUTPUT FORMATTING:
- Default to clean GitHub-flavored markdown.
- For multi-step answers use numbered or bulleted lists with short lines.
- For comparisons use tables when it helps.
- For SQL results from the database tool, summarize in a markdown table when small (<= 12 rows), otherwise summarize the shape and show the first few rows.
- Do NOT use the lowercase-bullet template from restricted mode. You are in admin mode.
- Do NOT expose raw SQL you ran unless the user asks; explain what you queried in plain language and show results.

DATABASE BEHAVIOR (very important — never guess data, ALWAYS query):
- ANY question about counts, lists, status, recency, area, reporter, or "how many" MUST trigger a `database_select` tool call. Do not answer from general knowledge.
- Examples that MUST trigger `database_select`:
  * "how many issues" / "how many reports" / "how many incidents" / "berapa banyak" → run a COUNT query.
  * "list pending" / "show resolved" / "what's in progress" → run a SELECT with a status filter.
  * "issues in jerudong" / "reports near berakas" → run a SELECT with a LIKE on address/location/district/mukim.
  * "latest 5 reports" / "recent submissions" → SELECT ... ORDER BY created_at DESC LIMIT N.
- Pick the correct connection from DATABASE CONTEXT (default is `mysql`).
- Read-only: SELECT only. If the user asks for INSERT/UPDATE/DELETE/DDL, politely decline and explain that admin mode is read-only by design, and suggest the safer path (e.g. open the admin panel route, or write a migration).
- Never invent table or column names — if a name is not in DATABASE CONTEXT, say you can't find it and ask for clarification.

BUSINESS DOMAIN MAPPING (use these when the user says generic words):
- "issue(s)" / "incident(s)" / "report(s)" / "case(s)" map to the union of these real tables:
  * `reports` — customer-submitted reports (columns include id, reference_code, problem_type, status, severity, address, latitude, longitude, reporter_name, phone, created_at).
  * `incidents` — customer photo submissions reviewed by AI (columns include id, user_id, photo_path, review_status, ai_label, ai_severity, admin_message, created_at). Note: NO latitude/longitude on this table.
  * `operations_reports` — admin-side ops records (columns include id, report_number, issue_type, severity, status, location_address, district, mukim, latitude, longitude, created_at).
  * `work_orders` — admin-side work orders (columns include id, work_order_number, type, priority, status, location_address, district, mukim, latitude, longitude, created_at).
- For "how many issues" without a filter, prefer querying all four tables and returning the totals (one COUNT per table) plus a grand total. Use `UNION ALL` over `SELECT 'reports' AS source, COUNT(*) AS n FROM reports` etc., or run them separately if simpler.
- Status terms map to columns:
  * `reports.status`, `operations_reports.status`, `work_orders.status` → values like 'pending', 'in_progress', 'resolved', 'under_review'.
  * `incidents.review_status` → values like 'PENDING_AI', 'APPROVED', 'REJECTED'. Match case-insensitively when filtering.
- Areas (jerudong, berakas, tutong, etc.) map to text fields: `reports.address`, `operations_reports.location_address` + `district` + `mukim`, `work_orders.location_address` + `district` + `mukim`. Use `LOWER(...) LIKE '%area%'`.

CAPABILITY DISCLOSURE:
- If the user asks what you can do / your instructions / "list all capabilities", give a clean markdown list covering: project context awareness, read-only DB queries, runnable code generation across the stack, debugging help, security review, refactoring, performance review, and Brunei drainage support fallback.

KNOWN BRUNEI DIRECTORY (use when relevant):
- website: www.pwd.gov.bn
- talian darussalam: 123
- facebook: @JKRBrunei | instagram: @jkrbrunei
- roads: @jkrbrunei_jalanraya | water: @jkrbrunei_air | drainage/sewerage: @jkrbrunei_saliran_pembetungan
- emergency: ambulance 991, police 993, fire 995, search & rescue 998
- water hotline: 140

You are warm, polite, and concise. Help the user ship.
TXT;
    }

    private function buildAppContext(): string
    {
        $lines = [];
        $lines[] = 'PROJECT CONTEXT (snapshot for admin/unrestricted mode):';
        $lines[] = '- App: BruDMS — Brunei Drainage Management System (sewage / drainage incident reporting + ops).';
        $lines[] = '- Framework: Laravel '.app()->version().' on PHP '.PHP_VERSION.'.';
        $lines[] = '- Default DB connection: `'.(string) config('database.default').'`'
            .' — database: `'.(string) config('database.connections.'.config('database.default').'.database').'`.';
        $lines[] = '- Auth + roles: Spatie Permission. Roles: super_admin, admin, operator, customer.';
        $lines[] = '- AI: OpenAI Chat Completions (model: `'.(string) config('services.openai.model', 'gpt-4o-mini').'`). System prompt is built in App\\Http\\Controllers\\Customer\\ChatController.';
        $lines[] = '- Frontend: Blade + vanilla JS (`resources/views/r_customer/bruflowgpt.blade.php` is the chat UI).';
        $lines[] = '- Map / geo: Brunei bounds + mukim/district config in `config/brunei.php`. Geolocation prefetch happens in the chat blade.';
        $lines[] = '- Customer-side reporting tables: `reports`, `incidents`. Admin-side: `operations_reports`, `work_orders`, `crews`, `workers`, `worker_attendances`, `work_order_photos`.';

        $controllers = $this->scanClassesIn(app_path('Http/Controllers'));
        if ($controllers !== []) {
            $lines[] = '- Controllers (top): '.implode(', ', array_slice($controllers, 0, 20)).'.';
        }

        $services = $this->scanClassesIn(app_path('Services'));
        if ($services !== []) {
            $lines[] = '- Services: '.implode(', ', array_slice($services, 0, 15)).'.';
        }

        $models = $this->scanClassesIn(app_path('Models'));
        if ($models !== []) {
            $lines[] = '- Models: '.implode(', ', array_slice($models, 0, 20)).'.';
        }

        $routes = $this->scanRoutes(30);
        if ($routes !== []) {
            $lines[] = '- Top routes:';
            foreach ($routes as $r) {
                $lines[] = '  • '.$r;
            }
        }

        $lines[] = '';
        $lines[] = 'Use this context to answer accurately. Never invent classes, routes, or columns not listed here or in DATABASE CONTEXT.';

        return implode("\n", $lines);
    }

    /**
     * @return list<string>
     */
    private function scanClassesIn(string $dir): array
    {
        if (! is_dir($dir)) {
            return [];
        }
        $names = [];
        try {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if (! $file instanceof \SplFileInfo || $file->getExtension() !== 'php') {
                    continue;
                }
                $name = $file->getBasename('.php');
                if ($name !== '' && $name !== 'Controller') {
                    $names[] = $name;
                }
            }
        } catch (\Throwable) {
            return [];
        }
        sort($names);

        return array_values(array_unique($names));
    }

    /**
     * @return list<string>
     */
    private function scanRoutes(int $limit = 30): array
    {
        $limit = max(5, min(100, $limit));
        try {
            $collection = \Illuminate\Support\Facades\Route::getRoutes();
        } catch (\Throwable) {
            return [];
        }

        $rows = [];
        foreach ($collection as $route) {
            try {
                $methods = $route->methods();
                $method = is_array($methods) ? implode('|', array_diff($methods, ['HEAD'])) : 'GET';
                $uri = '/'.ltrim((string) $route->uri(), '/');
                if (str_starts_with($uri, '/_') || str_contains($uri, 'sanctum') || str_contains($uri, 'telescope') || str_contains($uri, 'horizon')) {
                    continue;
                }
                $name = (string) ($route->getName() ?? '');
                $rows[] = trim($method.' '.$uri.($name !== '' ? ' ('.$name.')' : ''));
            } catch (\Throwable) {
                continue;
            }
            if (count($rows) >= $limit) {
                break;
            }
        }

        return $rows;
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
                    'name' => 'database_select',
                    'description' => 'Run a read-only SELECT on a Laravel database connection configured in this app. Use the exact connection name from DATABASE CONTEXT (e.g. mysql, sqlite). Omit connection to use the default.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'connection' => [
                                'type' => 'string',
                                'description' => 'Optional Laravel connection name from config/database.php',
                            ],
                            'sql' => [
                                'type' => 'string',
                                'description' => 'One read-only SELECT statement only.',
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
        int $maxRows,
        int $maxTokens = 1024
    ): string {
        for ($round = 0; $round < $maxRounds; $round++) {
            $payload = [
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => max(256, min(4096, $maxTokens)),
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
        if ($name !== 'database_select') {
            return json_encode(['error' => 'Unknown tool: '.$name]);
        }

        $sql = isset($args['sql']) && is_string($args['sql']) ? $args['sql'] : '';
        $connection = isset($args['connection']) && is_string($args['connection']) ? $args['connection'] : null;

        $result = $dbBridge->runSql($connection ?? '', $sql, $maxRows);

        if (! ($result['ok'] ?? false)) {
            return json_encode(['error' => $result['error'] ?? 'Query failed'], JSON_UNESCAPED_UNICODE);
        }

        $rows = $result['rows'] ?? [];
        $encoded = json_encode(
            [
                'rows' => $rows,
                'truncated' => (bool) ($result['truncated'] ?? false),
                'row_count' => count($rows),
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

    private function isGisIssueLookupRequest(string $message): bool
    {
        $message = strtolower(trim($message));
        if ($message === '') {
            return false;
        }

        return (bool) preg_match(
            '/\b(issue|issues|incident|incidents|report|reports|gis|map|nearby|near|dekat|sekitar)\b/i',
            $message
        );
    }

    private function isCountingQuestion(string $message): bool
    {
        $m = strtolower(trim($message));
        if ($m === '') {
            return false;
        }

        return (bool) preg_match(
            '/\b(how\s+many|how\s+much|count|total|number\s+of|berapa\s+banyak|berapa|jumlah|bilangan)\b/i',
            $m
        );
    }

    private function isBareCountFollowUp(string $message): bool
    {
        $m = strtolower(trim($message));
        $m = rtrim($m, " \t\n\r\0\x0B?.!");
        if ($m === '') {
            return false;
        }

        $patterns = [
            '/^how\s+many(\s+(are|is|of)?\s*(there|them|it|they))?$/i',
            '/^how\s+many\s+in\s+total$/i',
            '/^total$/i',
            '/^count$/i',
            '/^berapa(\s+banyak)?$/i',
            '/^jumlah$/i',
            '/^bilangan$/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $m)) {
                return true;
            }
        }

        return false;
    }

    private function mentionsDatabaseStructure(string $message): bool
    {
        $m = strtolower(trim($message));
        if ($m === '') {
            return false;
        }

        return (bool) preg_match(
            '/\b(table|tables|column|columns|schema|database|db|row|rows)\b/i',
            $m
        );
    }

    private function extractBruneiAreaFromMessage(string $message): ?string
    {
        $normalized = strtolower(trim($message));
        if ($normalized === '') {
            return null;
        }

        $areas = $this->bruneiAreaCandidates();

        usort($areas, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        foreach ($areas as $area) {
            if ($area === '') {
                continue;
            }
            $pattern = '/\b'.preg_quote($area, '/').'\b/iu';
            if (preg_match($pattern, $normalized)) {
                return $area;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function bruneiAreaCandidates(): array
    {
        $areas = [
            'jerudong', 'berakas', 'gadong', 'kiulap', 'kiarong', 'mentiri', 'kota batu',
            'sengkurong', 'mata-mata', 'rimba', 'tanjung bunut', 'bunut', 'lambak', 'lambak kiri',
            'manggis', 'subok', 'serasa', 'muara', 'kilanas', 'tungku', 'tungku link',
            'bandar seri begawan', 'bsb', 'kuala belait', 'kb', 'seria', 'tutong',
            'pekan tutong', 'bangar', 'temburong', 'panaga', 'lumut', 'sungai liang',
            'liang', 'lumapas', 'limau manis', 'salambigar', 'rimba', 'beribi',
            'kampong ayer', 'kampung ayer',
        ];

        foreach ((array) config('brunei.districts', []) as $key => $district) {
            if (is_string($district) && $district !== '') {
                $areas[] = strtolower($district);
                $areas[] = strtolower(str_replace('-', ' ', $district));
            }
            if (is_string($key) && $key !== '') {
                $areas[] = strtolower(str_replace('-', ' ', $key));
            }
        }

        foreach ((array) config('brunei.mukims', []) as $group) {
            if (! is_array($group)) {
                continue;
            }
            foreach ($group as $key => $mukim) {
                if (is_string($mukim) && $mukim !== '') {
                    $full = strtolower($mukim);
                    $areas[] = $full;
                    $stripped = trim(preg_replace("/\s*'[ab]'\s*$/iu", '', $full) ?? $full);
                    if ($stripped !== '' && $stripped !== $full) {
                        $areas[] = $stripped;
                    }
                    $cleaned = trim(str_replace(["'", '"'], '', $full));
                    if ($cleaned !== '' && $cleaned !== $full) {
                        $areas[] = $cleaned;
                    }
                }
                if (is_string($key) && $key !== '') {
                    $slugSpace = strtolower(str_replace('-', ' ', $key));
                    $areas[] = $slugSpace;
                    $strippedSlug = trim(preg_replace('/\s+[ab]$/iu', '', $slugSpace) ?? $slugSpace);
                    if ($strippedSlug !== '' && $strippedSlug !== $slugSpace) {
                        $areas[] = $strippedSlug;
                    }
                }
            }
        }

        $areas = array_filter($areas, static fn (string $a): bool => trim($a) !== '');
        $areas = array_map(static fn (string $a): string => trim($a), $areas);

        return array_values(array_unique($areas));
    }

    private function extractExcludedAreaFromMessage(string $message, string $rememberedArea = ''): ?string
    {
        $m = strtolower(trim($message));
        if ($m === '') {
            return null;
        }

        if (preg_match('/\bnot\s+([a-z][a-z\- ]{2,40})\b/i', $m, $match)) {
            $candidate = trim((string) $match[1]);
            $candidate = preg_replace('/\b(issue|issues|area|areas|in|near|nearby|please|thanks)\b/i', '', $candidate ?? '');
            $candidate = trim((string) $candidate);
            if ($candidate !== '') {
                return $candidate;
            }
        }

        if ((str_contains($m, 'anywhere else') || str_contains($m, 'area lain') || str_contains($m, 'elsewhere')) && $rememberedArea !== '') {
            return strtolower(trim($rememberedArea));
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function extractRequestedStatuses(string $message): array
    {
        $m = strtolower(trim($message));
        if ($m === '') {
            return [];
        }

        $statuses = [];
        if (str_contains($m, 'under review') || str_contains($m, 'under_review')) {
            $statuses[] = 'under_review';
        }
        if (str_contains($m, 'pending')) {
            $statuses[] = 'pending';
        }
        if (str_contains($m, 'in progress') || str_contains($m, 'in_progress')) {
            $statuses[] = 'in_progress';
        }
        if (str_contains($m, 'resolved')) {
            $statuses[] = 'resolved';
        }

        return array_values(array_unique($statuses));
    }

    private function buildAreaIssueCountReply(string $area, ZiqahDatabaseBridge $dbBridge): ?string
    {
        $area = trim($area);
        if ($area === '') {
            return null;
        }

        $safeArea = str_replace("'", "''", strtolower($area));
        $safeAreaSlug = str_replace("'", "''", str_replace(' ', '-', $safeArea));

        $sql = "SELECT
                    (SELECT COUNT(*) FROM reports
                        WHERE LOWER(COALESCE(address, '')) LIKE '%{$safeArea}%') AS reports_count,
                    (SELECT COUNT(*) FROM incidents
                        WHERE LOWER(COALESCE(admin_message, '')) LIKE '%{$safeArea}%'
                           OR LOWER(COALESCE(CAST(ai_reasons AS CHAR), '')) LIKE '%{$safeArea}%'
                           OR LOWER(COALESCE(CAST(evidence AS CHAR), '')) LIKE '%{$safeArea}%'
                           OR LOWER(COALESCE(CAST(proof AS CHAR), '')) LIKE '%{$safeArea}%') AS incidents_count,
                    (SELECT COUNT(*) FROM operations_reports
                        WHERE LOWER(COALESCE(location_address, '')) LIKE '%{$safeArea}%'
                           OR LOWER(COALESCE(district, '')) LIKE '%{$safeArea}%'
                           OR LOWER(COALESCE(mukim, '')) LIKE '%{$safeArea}%'
                           OR LOWER(COALESCE(district, '')) LIKE '%{$safeAreaSlug}%'
                           OR LOWER(COALESCE(mukim, '')) LIKE '%{$safeAreaSlug}%') AS operations_count,
                    (SELECT COUNT(*) FROM work_orders
                        WHERE LOWER(COALESCE(location_address, '')) LIKE '%{$safeArea}%'
                           OR LOWER(COALESCE(district, '')) LIKE '%{$safeArea}%'
                           OR LOWER(COALESCE(mukim, '')) LIKE '%{$safeArea}%'
                           OR LOWER(COALESCE(district, '')) LIKE '%{$safeAreaSlug}%'
                           OR LOWER(COALESCE(mukim, '')) LIKE '%{$safeAreaSlug}%') AS work_orders_count";

        $result = $dbBridge->runSql('', $sql, 1);
        if (! ($result['ok'] ?? false)) {
            return null;
        }

        $rows = $result['rows'] ?? [];
        if (! is_array($rows) || $rows === []) {
            return null;
        }

        $row = $rows[0];
        $get = static function ($row, string $field): int {
            $v = is_object($row) ? ($row->{$field} ?? 0) : ($row[$field] ?? 0);

            return (int) $v;
        };

        $total = $get($row, 'reports_count')
            + $get($row, 'incidents_count')
            + $get($row, 'operations_count')
            + $get($row, 'work_orders_count');

        if ($total === 0) {
            return "there are no issues in {$area}.";
        }

        if ($total === 1) {
            return "there is 1 issue in {$area}.";
        }

        return "there are {$total} issues in {$area}.";
    }

    private function buildGisIssueDetailsReply(string $area, string $message, ZiqahDatabaseBridge $dbBridge, int $limit = 20, ?string $excludedArea = null): ?string
    {
        $area = trim($area);
        if ($area === '') {
            return null;
        }

        $limit = max(1, min(50, $limit));
        $safeArea = str_replace("'", "''", strtolower($area));
        $safeAreaSlug = str_replace("'", "''", str_replace(' ', '-', $safeArea));
        $requestedStatuses = $this->extractRequestedStatuses($message);
        $statusSql = '';
        $incidentStatusSql = '';
        if ($requestedStatuses !== []) {
            $quoted = array_map(static fn (string $s): string => "'".$s."'", $requestedStatuses);
            $statusSql = ' AND LOWER(COALESCE(status, \'\')) IN ('.implode(',', $quoted).')';
            $incidentStatusSql = ' AND LOWER(COALESCE(review_status, \'\')) IN ('.implode(',', $quoted).')';
        }
        $excludeCustomerSql = '';
        $excludeAdminSql = '';
        $excludeIncidentSql = '';
        if ($excludedArea !== null && trim($excludedArea) !== '') {
            $safeExcluded = str_replace("'", "''", strtolower(trim($excludedArea)));
            $safeExcludedSlug = str_replace("'", "''", str_replace(' ', '-', $safeExcluded));
            $excludeCustomerSql = " AND LOWER(COALESCE(address, '')) NOT LIKE '%{$safeExcluded}%'";
            $excludeAdminSql = " AND LOWER(COALESCE(location_address, '')) NOT LIKE '%{$safeExcluded}%'
                                 AND LOWER(COALESCE(district, '')) NOT LIKE '%{$safeExcluded}%'
                                 AND LOWER(COALESCE(mukim, '')) NOT LIKE '%{$safeExcluded}%'
                                 AND LOWER(COALESCE(district, '')) NOT LIKE '%{$safeExcludedSlug}%'
                                 AND LOWER(COALESCE(mukim, '')) NOT LIKE '%{$safeExcludedSlug}%'";
            $excludeIncidentSql = " AND LOWER(COALESCE(admin_message, '')) NOT LIKE '%{$safeExcluded}%'
                                    AND LOWER(COALESCE(CAST(ai_reasons AS CHAR), '')) NOT LIKE '%{$safeExcluded}%'
                                    AND LOWER(COALESCE(CAST(evidence AS CHAR), '')) NOT LIKE '%{$safeExcluded}%'
                                    AND LOWER(COALESCE(CAST(proof AS CHAR), '')) NOT LIKE '%{$safeExcluded}%'";
        }

        $customerSql = "SELECT * FROM (
                            SELECT
                                COALESCE(reference_code, CONCAT('#', id)) AS reference,
                                COALESCE(status, 'unknown') AS status,
                                COALESCE(problem_type, 'issue') AS problem_type,
                                COALESCE(severity, '') AS severity,
                                COALESCE(reporter_name, '') AS reporter,
                                COALESCE(phone, '') AS phone,
                                COALESCE(address, '') AS address,
                                latitude AS latitude,
                                longitude AS longitude,
                                created_at AS submitted,
                                'reports' AS source
                            FROM reports
                            WHERE LOWER(COALESCE(address, '')) LIKE '%{$safeArea}%'
                            {$excludeCustomerSql}
                            {$statusSql}

                            UNION ALL

                            SELECT
                                CONCAT('INC-', id) AS reference,
                                COALESCE(review_status, 'unknown') AS status,
                                COALESCE(ai_label, 'incident') AS problem_type,
                                COALESCE(ai_severity, '') AS severity,
                                '' AS reporter,
                                '' AS phone,
                                COALESCE(admin_message, '') AS address,
                                NULL AS latitude,
                                NULL AS longitude,
                                created_at AS submitted,
                                'incidents' AS source
                            FROM incidents
                            WHERE (
                                LOWER(COALESCE(admin_message, '')) LIKE '%{$safeArea}%'
                                OR LOWER(COALESCE(CAST(ai_reasons AS CHAR), '')) LIKE '%{$safeArea}%'
                                OR LOWER(COALESCE(CAST(evidence AS CHAR), '')) LIKE '%{$safeArea}%'
                                OR LOWER(COALESCE(CAST(proof AS CHAR), '')) LIKE '%{$safeArea}%'
                            )
                            {$excludeIncidentSql}
                            {$incidentStatusSql}
                        ) x
                        ORDER BY submitted DESC
                        LIMIT {$limit}";

        $result = $dbBridge->runSql('', $customerSql, $limit);
        if (! ($result['ok'] ?? false)) {
            return null;
        }

        $rows = $result['rows'] ?? [];
        if (! is_array($rows)) {
            $rows = [];
        }

        // If customer-side reports have no match, fallback to admin-side GIS datasets.
        if ($rows === []) {
            $adminSql = "SELECT * FROM (
                            SELECT
                                COALESCE(report_number, CONCAT('#', id)) AS reference,
                                COALESCE(status, 'unknown') AS status,
                                COALESCE(issue_type, 'issue') AS problem_type,
                                COALESCE(severity, '') AS severity,
                                COALESCE(reporter_name, '') AS reporter,
                                COALESCE(reporter_contact, '') AS phone,
                                COALESCE(location_address, '') AS address,
                                latitude AS latitude,
                                longitude AS longitude,
                                created_at AS submitted,
                                'operations_reports' AS source
                            FROM operations_reports
                            WHERE (
                                LOWER(COALESCE(location_address, '')) LIKE '%{$safeArea}%'
                                OR LOWER(COALESCE(district, '')) LIKE '%{$safeArea}%'
                                OR LOWER(COALESCE(mukim, '')) LIKE '%{$safeArea}%'
                                OR LOWER(COALESCE(district, '')) LIKE '%{$safeAreaSlug}%'
                                OR LOWER(COALESCE(mukim, '')) LIKE '%{$safeAreaSlug}%'
                            )
                            {$excludeAdminSql}
                            {$statusSql}

                            UNION ALL

                            SELECT
                                COALESCE(work_order_number, CONCAT('#', id)) AS reference,
                                COALESCE(status, 'unknown') AS status,
                                COALESCE(type, 'issue') AS problem_type,
                                COALESCE(priority, '') AS severity,
                                '' AS reporter,
                                '' AS phone,
                                COALESCE(location_address, '') AS address,
                                latitude AS latitude,
                                longitude AS longitude,
                                created_at AS submitted,
                                'work_orders' AS source
                            FROM work_orders
                            WHERE (
                                LOWER(COALESCE(location_address, '')) LIKE '%{$safeArea}%'
                                OR LOWER(COALESCE(district, '')) LIKE '%{$safeArea}%'
                                OR LOWER(COALESCE(mukim, '')) LIKE '%{$safeArea}%'
                                OR LOWER(COALESCE(district, '')) LIKE '%{$safeAreaSlug}%'
                                OR LOWER(COALESCE(mukim, '')) LIKE '%{$safeAreaSlug}%'
                            )
                            {$excludeAdminSql}
                            {$statusSql}

                        ) x
                        ORDER BY submitted DESC
                        LIMIT {$limit}";

            $adminResult = $dbBridge->runSql('', $adminSql, $limit);
            if (! ($adminResult['ok'] ?? false)) {
                return null;
            }
            $rows = is_array($adminResult['rows'] ?? null) ? $adminResult['rows'] : [];
        }

        if (! is_array($rows) || $rows === []) {
            if ($excludedArea !== null && trim($excludedArea) !== '') {
                return 'there is no incident in that area.';
            }
            return 'there is no incident in that area.';
        }

        $lines = ["issues near {$area}", "────────", ""];
        foreach ($rows as $row) {
            $reference = is_object($row) ? (string) ($row->reference ?? 'n/a') : (string) ($row['reference'] ?? 'n/a');
            $status = is_object($row) ? (string) ($row->status ?? 'n/a') : (string) ($row['status'] ?? 'n/a');
            $problemType = is_object($row) ? (string) ($row->problem_type ?? 'n/a') : (string) ($row['problem_type'] ?? 'n/a');
            $severity = is_object($row) ? (string) ($row->severity ?? 'n/a') : (string) ($row['severity'] ?? 'n/a');
            $reporter = is_object($row) ? (string) ($row->reporter ?? 'n/a') : (string) ($row['reporter'] ?? 'n/a');
            $phone = is_object($row) ? (string) ($row->phone ?? 'n/a') : (string) ($row['phone'] ?? 'n/a');
            $address = is_object($row) ? (string) ($row->address ?? 'n/a') : (string) ($row['address'] ?? 'n/a');
            $lat = is_object($row) ? ($row->latitude ?? null) : ($row['latitude'] ?? null);
            $lng = is_object($row) ? ($row->longitude ?? null) : ($row['longitude'] ?? null);
            $submitted = is_object($row) ? (string) ($row->submitted ?? 'n/a') : (string) ($row['submitted'] ?? 'n/a');

            $coords = ($lat !== null && $lng !== null) ? ((string) $lat.', '.(string) $lng) : 'n/a';
            $lines[] = '• details';
            $lines[] = '• reference: '.$reference;
            $lines[] = '• status: '.$status;
            $lines[] = '• problem type: '.$problemType;
            $lines[] = '• severity: '.($severity !== '' ? $severity : 'n/a');
            $lines[] = '• reporter: '.($reporter !== '' ? $reporter : 'n/a');
            $lines[] = '• phone: '.($phone !== '' ? $phone : 'n/a');
            $lines[] = '• address: '.($address !== '' ? $address : 'n/a');
            $lines[] = '• coordinates: '.$coords;
            $lines[] = '• submitted: '.($submitted !== '' ? $submitted : 'n/a');
            $lines[] = '';
        }

        return implode("\n", $lines);
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

    private function buildNearbyIssuesReply(
        float $userLat,
        float $userLng,
        ZiqahDatabaseBridge $dbBridge,
        int $limit = 8,
        float $maxKm = 10.0
    ): ?string {
        $limit = max(1, min(20, $limit));
        $maxKm = max(0.5, min(50.0, $maxKm));

        $sql = "SELECT * FROM (
                    SELECT
                        COALESCE(reference_code, CONCAT('#', id)) AS reference,
                        COALESCE(status, 'unknown') AS status,
                        COALESCE(problem_type, 'issue') AS problem_type,
                        COALESCE(severity, '') AS severity,
                        COALESCE(reporter_name, '') AS reporter,
                        COALESCE(phone, '') AS phone,
                        COALESCE(address, '') AS address,
                        latitude AS latitude,
                        longitude AS longitude,
                        created_at AS submitted,
                        'reports' AS source
                    FROM reports
                    WHERE latitude IS NOT NULL AND longitude IS NOT NULL

                    UNION ALL

                    SELECT
                        COALESCE(report_number, CONCAT('#', id)) AS reference,
                        COALESCE(status, 'unknown') AS status,
                        COALESCE(issue_type, 'issue') AS problem_type,
                        COALESCE(severity, '') AS severity,
                        COALESCE(reporter_name, '') AS reporter,
                        COALESCE(reporter_contact, '') AS phone,
                        COALESCE(location_address, '') AS address,
                        latitude AS latitude,
                        longitude AS longitude,
                        created_at AS submitted,
                        'operations_reports' AS source
                    FROM operations_reports
                    WHERE latitude IS NOT NULL AND longitude IS NOT NULL

                    UNION ALL

                    SELECT
                        COALESCE(work_order_number, CONCAT('#', id)) AS reference,
                        COALESCE(status, 'unknown') AS status,
                        COALESCE(type, 'issue') AS problem_type,
                        COALESCE(priority, '') AS severity,
                        '' AS reporter,
                        '' AS phone,
                        COALESCE(location_address, '') AS address,
                        latitude AS latitude,
                        longitude AS longitude,
                        created_at AS submitted,
                        'work_orders' AS source
                    FROM work_orders
                    WHERE latitude IS NOT NULL AND longitude IS NOT NULL
                ) x
                ORDER BY submitted DESC
                LIMIT 600";

        $result = $dbBridge->runSql('', $sql, 600);
        if (! ($result['ok'] ?? false)) {
            return null;
        }

        $rows = $result['rows'] ?? [];
        if (! is_array($rows)) {
            $rows = [];
        }

        $scored = [];
        foreach ($rows as $row) {
            $lat = is_object($row) ? ($row->latitude ?? null) : ($row['latitude'] ?? null);
            $lng = is_object($row) ? ($row->longitude ?? null) : ($row['longitude'] ?? null);
            if ($lat === null || $lng === null) {
                continue;
            }
            $km = $this->haversineKm($userLat, $userLng, (float) $lat, (float) $lng);
            if ($km > $maxKm) {
                continue;
            }
            $scored[] = ['km' => $km, 'row' => $row];
        }

        usort($scored, static fn (array $a, array $b): int => $a['km'] <=> $b['km']);
        $scored = array_slice($scored, 0, $limit);

        if ($scored === []) {
            return 'there is no incident near you.';
        }

        $lines = ['issues near you', '────────', ''];
        foreach ($scored as $entry) {
            $row = $entry['row'];
            $km = $entry['km'];
            $reference = is_object($row) ? (string) ($row->reference ?? 'n/a') : (string) ($row['reference'] ?? 'n/a');
            $status = is_object($row) ? (string) ($row->status ?? 'n/a') : (string) ($row['status'] ?? 'n/a');
            $problemType = is_object($row) ? (string) ($row->problem_type ?? 'n/a') : (string) ($row['problem_type'] ?? 'n/a');
            $severity = is_object($row) ? (string) ($row->severity ?? '') : (string) ($row['severity'] ?? '');
            $reporter = is_object($row) ? (string) ($row->reporter ?? '') : (string) ($row['reporter'] ?? '');
            $phone = is_object($row) ? (string) ($row->phone ?? '') : (string) ($row['phone'] ?? '');
            $address = is_object($row) ? (string) ($row->address ?? '') : (string) ($row['address'] ?? '');
            $lat = is_object($row) ? ($row->latitude ?? null) : ($row['latitude'] ?? null);
            $lng = is_object($row) ? ($row->longitude ?? null) : ($row['longitude'] ?? null);
            $submitted = is_object($row) ? (string) ($row->submitted ?? '') : (string) ($row['submitted'] ?? '');

            $coords = ($lat !== null && $lng !== null) ? ((string) $lat.', '.(string) $lng) : 'n/a';

            $lines[] = '• details';
            $lines[] = '• reference: '.($reference !== '' ? $reference : 'n/a');
            $lines[] = '• status: '.($status !== '' ? $status : 'n/a');
            $lines[] = '• problem type: '.($problemType !== '' ? $problemType : 'n/a');
            $lines[] = '• severity: '.($severity !== '' ? $severity : 'n/a');
            $lines[] = '• reporter: '.($reporter !== '' ? $reporter : 'n/a');
            $lines[] = '• phone: '.($phone !== '' ? $phone : 'n/a');
            $lines[] = '• address: '.($address !== '' ? $address : 'n/a');
            $lines[] = '• coordinates: '.$coords;
            $lines[] = '• distance: ~'.number_format($km, 1).' km';
            $lines[] = '• submitted: '.($submitted !== '' ? $submitted : 'n/a');
            $lines[] = '';
        }

        return rtrim(implode("\n", $lines));
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earth * $c;
    }
}