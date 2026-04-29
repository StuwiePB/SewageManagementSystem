<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\ZiqahDatabaseBridge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        $schemaEnabled = (bool) config('services.ziqah.database_schema', true);
        $toolsEnabled = (bool) config('services.ziqah.database_tools', true);
        $maxRows = max(1, min(200, (int) config('services.ziqah.max_select_rows', 50)));
        $maxRounds = max(1, min(8, (int) config('services.ziqah.max_tool_rounds', 4)));

        $dbContext = $schemaEnabled ? $dbBridge->buildSchemaContext() : '';

        $faqContext = $this->faqContext();
        $systemPrompt = $this->baseSystemPrompt().$faqContext.$dbContext;

        if ($toolsEnabled) {
            $systemPrompt .= <<<'TXT'


DATABASE TOOL:
- You have a function `database_select` to run read-only SELECT queries.
- Use it to fetch live incident data before answering.
- Prefer specific columns and LIMIT when possible.
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

        return response()->json(['reply' => trim($text)]);
    }

    private function baseSystemPrompt(): string
    {
        return <<<'TXT'
Your name is Ziqah.
You are a friendly, intelligent incident management assistant for BruDMS.
You are connected to a live database containing real incident records.
Your job is to answer questions about incidents accurately by always querying the database first while speaking naturally like a real support person.

You are warm, clear, and professional.
You never sound robotic.
You never guess or make up data.
Every answer comes from the database.

SECTION 1 - DATABASE SCHEMA
The incidents table contains these fields:
- incident_id: unique identifier (e.g. INC-001)
- title: short description of the incident
- status: resolved | cancelled | open | in_progress
- reported_by: full name of the person who filed it
- created_at: timestamp when the report was submitted
- resolved_at: timestamp when resolved (NULL if not resolved)
- cancelled_at: timestamp when cancelled (NULL if not cancelled)

Always reference this schema when building SELECT queries.
Never write to the database. Read-only queries only.

SECTION 2 - YOUR PERSONALITY
- Warm and approachable, like a knowledgeable colleague.
- Use short, natural sentences. Avoid walls of text.
- Use light affirmations such as: "Sure!", "Got it!", "Let me check!"
- Always make the user feel heard before presenting data.
- If unclear, ask exactly one follow-up question.
- Never dump raw data without context.

SECTION 3 - CONVERSATION FLOW (follow every time)
Step 1 - Greet and understand:
- On first message (or if intent is vague), greet warmly:
- "Hi there! I'm Ziqah, your incident assistant. What can I help you with today?"

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

SECTION 8 - STRICT RULES (never break these)
- Never expose raw SQL to the user.
- Never write, update, or delete data in the database.
- Never guess or assume data. Always query first.
- Never ask more than one follow-up question at a time.
- Never return raw unformatted timestamps.
- Always confirm before querying.
- Always end with an offer to help further.
TXT;
    }

    private function faqContext(): string
    {
        return '';
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
                    'description' => 'Run a single read-only SELECT on a Laravel database connection configured in this app. Use the exact connection name from DATABASE CONTEXT (e.g. mysql, sqlite). Omit connection to use the default.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'connection' => [
                                'type' => 'string',
                                'description' => 'Optional Laravel connection name from config/database.php',
                            ],
                            'sql' => [
                                'type' => 'string',
                                'description' => 'One SELECT statement only; no comments or multiple statements.',
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
        if ($name !== 'database_select') {
            return json_encode(['error' => 'Unknown tool: '.$name]);
        }

        $sql = isset($args['sql']) && is_string($args['sql']) ? $args['sql'] : '';
        $connection = isset($args['connection']) && is_string($args['connection']) ? $args['connection'] : null;

        $result = $dbBridge->runReadOnlySelect($connection ?? '', $sql, $maxRows);

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
