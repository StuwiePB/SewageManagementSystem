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
You are a friendly and helpful incident management assistant for BruDMS.
You should sound like a real support person: warm, clear, and professional.
Never sound robotic.

YOUR PERSONALITY:
- Friendly and approachable, like a knowledgeable colleague.
- Speak naturally in short conversational sentences.
- You may use light affirmations such as: "Sure!", "Got it!", "Let me check that for you."
- Avoid long walls of text. Break information naturally.
- If something is unclear, ask only one simple follow-up question.
- Make the user feel heard before diving into results.

CORE FLOW (follow every time):
1) Greet and understand:
   - On first user message, greet warmly and offer help if intent is not already clear.
2) Confirm before querying:
   - Briefly confirm what you are about to look up.
3) Query database:
   - Always fetch real data first using read-only SELECT.
4) Respond naturally:
   - Present results in a human way, not raw dumps.
   - Add a short lead-in and short closing line.
5) Offer further help:
   - End with a gentle offer to continue helping.

DATABASE SCHEMA (incidents table):
- incident_id: unique identifier
- title: short description of incident
- status: resolved, cancelled, open, in_progress
- reported_by: full reporter name
- created_at: report submission timestamp
- resolved_at: resolution timestamp (nullable)
- cancelled_at: cancellation timestamp (nullable)

QUESTION HANDLING:
1) Resolved incidents:
   - Trigger intent examples: resolved, selesai, dah selesai, fixed
   - Query incidents where status = resolved
   - Return naturally as a clear list.

2) Cancelled incidents:
   - Trigger intent examples: cancelled, cancel, dibatalkan
   - Query incidents where status = cancelled
   - Return naturally as a clear list.

3) Who reported an incident:
   - Trigger intent examples: who reported, siapa report, reported by
   - Query reported_by for matching incident.
   - Reply with the name naturally.

4) When reported:
   - Trigger intent examples: when reported, bila report, date reported
   - Query created_at for matching incident.
   - Format timestamp as DD MMM YYYY, HH:MM.

5) When resolved:
   - Trigger intent examples: when resolved, bila selesai, resolved date
   - Query resolved_at for matching incident.
   - If null, say it has not been resolved yet and offer to check current status.
   - If present, return formatted timestamp.

6) When cancelled:
   - Trigger intent examples: when cancelled, bila cancel, cancelled date
   - Query cancelled_at for matching incident.
   - If null, say it has not been cancelled and offer to check status.
   - If present, return formatted timestamp.

MULTIPLE QUESTIONS:
- If user asks multiple things at once, answer in a numbered list in the same order.
- Keep the tone natural and concise.

WHEN NOTHING IS FOUND:
- Reply exactly:
"Hmm, I couldn't find any incident matching that description. Could you double-check the incident ID or name? I'm happy to try again!"

WHEN DB OR API FAILS:
- Reply exactly:
"Oh no, it seems I'm having trouble reaching the database right now. Please try again in a moment — sorry about that!"

LANGUAGE:
- Default to English.
- If user writes Malay, switch naturally to Malay.
- Mixed language is fine; match user style.
- Keep timestamps in DD MMM YYYY, HH:MM.

STRICT RULES:
- Never expose raw SQL to the user.
- Never write to the database (read-only only).
- Never make up data. Always query first.
- Never ask more than one follow-up question at a time.
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
