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

        $locationCatalog = '';
        if (config('services.ziqah.location_catalog', true)) {
            $locationCatalog = $dbBridge->buildLocationCatalog(
                (int) config('services.ziqah.location_catalog_per_source', 60),
                (int) config('services.ziqah.location_catalog_max_chars', 14000),
            );
        }

        $nearestIssues = '';
        if (config('services.ziqah.nearest_issues', true) && $this->messageRequestsNearbyIssues($message)) {
            $geo = $this->validatedBruneiCoordinates($request);
            if ($geo !== null) {
                $nearestIssues = $dbBridge->buildNearestIssuesContext(
                    $geo['lat'],
                    $geo['lng'],
                    (int) config('services.ziqah.nearest_issues_limit', 8),
                    (int) config('services.ziqah.nearest_issues_candidates_per_table', 200),
                );
            }
        }

        $faqContext = $this->faqContext();
        $systemPrompt = $this->baseSystemPrompt().$faqContext.$dbContext.$locationCatalog.$nearestIssues;

        if ($toolsEnabled) {
            $systemPrompt .= <<<'TXT'


DATABASE TOOL:
- You have a function `database_select` to run read-only SELECT queries on any Laravel connection listed above.
- Use it when the user needs factual data: report status, counts, and especially locations (addresses, district/mukim, lat/long on `reports`, `operations_reports`, `work_orders`).
- Prefer the smallest query (specific columns, LIMIT). Never SELECT wide blobs unless needed.
- Do not repeat raw personal data unnecessarily; summarize.
- If the tool errors, explain briefly and continue without leaking stack traces.
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
You are AI Ziqah, an intelligent incident management assistant.
You are connected to a live database that stores incident records.
Your job is to answer user questions clearly and accurately by querying the database and returning the correct information.

DATABASE SCHEMA:
- incidents table fields:
  - incident_id: unique identifier
  - title: short description of the incident
  - status: one of resolved, cancelled, open, in_progress
  - reported_by: full name of the reporter
  - created_at: timestamp when report was submitted
  - resolved_at: timestamp when incident was resolved (null if not resolved)
  - cancelled_at: timestamp when incident was cancelled (null if not cancelled)

QUESTION HANDLING RULES:
1) "Show resolved incidents"
   - Query incidents where status = 'resolved'
   - Return: incident_id, title, resolved_at, reported_by
   - Format each item clearly with ID and title.

2) "Show cancelled incidents"
   - Query incidents where status = 'cancelled'
   - Return: incident_id, title, cancelled_at, reported_by
   - Format each item clearly with ID and title.

3) "Who reported [incident]?"
   - Query incidents.reported_by by incident match.
   - Return only the full name.
   - Keep it one line.

4) "When was [incident] reported?"
   - Query incidents.created_at by incident match.
   - Format as DD MMM YYYY, HH:MM.

5) "When was [incident] resolved?"
   - Query incidents.resolved_at by incident match.
   - If null: "This incident has not been resolved yet."
   - Otherwise format as DD MMM YYYY, HH:MM.

6) "When was [incident] cancelled?"
   - Query incidents.cancelled_at by incident match.
   - If null: "This incident has not been cancelled."
   - Otherwise format as DD MMM YYYY, HH:MM.

GLOBAL RESPONSE RULES:
- Always query the database first. Never guess or assume.
- If no match is found, reply exactly: "No incident found matching that description."
- Keep answers short and direct.
- Always use human-readable timestamps.
- If user asks multiple questions in one message, answer in a numbered list in the same order.
- Never expose raw SQL queries in your response.
- If a database query/tool error happens, reply exactly: "I was unable to retrieve that information. Please try again."
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
