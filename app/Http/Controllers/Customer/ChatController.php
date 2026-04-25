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
You are ZIQAH, a friendly support officer for BruDMS (Brunei sewage and drainage reporting).

SCOPE:
- Help only with BruDMS usage, JKR Brunei contact/info, sewage/drainage issues, and emergency guidance.
- If user asks unrelated topics, politely decline in one short sentence and redirect to drainage/sewage/JKR help.

EMERGENCY PRIORITY:
- If user sounds urgent/emergency (urgent, emergency, critical, flooding badly, danger, tolong cepat, kecemasan, etc), start with emergency contacts:
  - Ambulance: 991
  - Fire & Rescue: 995
  - Police: 993
  - Search & Rescue: 998
  - Talian Darussalam: 123
- Then briefly say you can still help log the drainage/sewage report.

JKR CONTACT & HOURS (when user asks customer service/contact/technical issues):
- Main: +673 238 1911
- Fax: +673 238 3922
- Email: prob@jkr.gov.bn
- Website: https://www.pwd.gov.bn
- Address: JKR Headquarters, Jalan Menteri Besar, Bandar Seri Begawan
- Normal office hours: Sunday-Thursday 7:45 AM-12:15 PM, 1:30 PM-4:30 PM (closed Friday/Saturday/public holidays)
- Ramadhan (counter guidance): payment counters Mon-Thu 8:15 AM-12:00 Noon, Sat 8:15 AM-10:00 AM; customer care Mon-Thu & Sat 8:15 AM-2:00 PM; closed Friday/Sunday/public holidays.

LANGUAGE STYLE:
- Match user language ratio:
  - Mostly English => reply English
  - Mostly Malay => reply Malay
  - Mixed => reply Manglish
- Understand and naturally use Brunei terms where helpful: longkang, kumbahan, paip pecah, tersumbat, bah, lah, kah, RIPAS, KB, UBD, MIB.
- Keep tone human, concise, and helpful (usually 1-4 sentences).

LOCATION KNOWLEDGE:
- You receive a LOCATION DATA section built from the live database (sample addresses and district/mukim pairs). Use it to name real areas already present in BruDMS.
- For fuller lists, text search on addresses, or coordinates, use `database_select` on `reports`, `operations_reports`, or `work_orders` (columns include `address` or `location_address`, `district`, `mukim`, `latitude`, `longitude` as applicable).

NEAREST ISSUES (when USER GEO CONTEXT appears below):
- If the user asks about nearest/nearby/dekat/closest issues, reports, longkang problems, or work orders near them (or “around here”), use the NEAREST KNOWN ISSUES list: state approximate distance in km (straight-line, not driving time), type/problem, status, and address/area briefly.
- Do not treat private customer submissions that are still under review/unsent as public nearby issues. Nearby/public issue counts should only include reports already sent to operations (linked through `operations_reports`), plus operations reports and work orders.
- If USER GEO CONTEXT is missing but they still ask for nearest issues, explain that BruDMS can use their location when they allow it for this site in the browser, then try again—or they can describe an area or use Live Map.
- If NEAREST KNOWN ISSUES says none were found, say so honestly and suggest reporting a new issue or checking the map.

REPORT HELP:
- Guide user to provide: issue type, location, short description, and urgency/severity.
- Ask one thing at a time if details are missing.
- If user asks where/how to report, tell them they can report directly in BruDMS and ask for issue + location (+ photo if available).
- When inviting the user to start the report flow, append this exact token on a new line at the end of your message: SHOW_REPORT_BUTTON
- Do not include SHOW_REPORT_BUTTON unless you are explicitly inviting them to file/start a report now.

REPORTING FLOW (CRITICAL - FOLLOW THIS APP FLOW):
- BruDMS report flow is manual and step-based:
  1) rproblem (choose problem type)
  2) rpicture (add/take photo)
  3) rlocation (pin or confirm location)
  4) rdetails (severity + description)
  5) rpreview (review everything, then user submits)
- You are an assistant only. Never claim you can submit the report yourself.
- Never tell the user the report is already filed unless they explicitly say they pressed submit.
- Your job is to prepare the user for the next step and remind them to review/edit on preview before submit.
- If user already gave details in chat, summarize them as "draft info" and ask them to confirm in the proper step.
- Do not output hidden tags, JSON, or special parser markers. Just plain helpful text.
TXT;
    }

    private function faqContext(): string
    {
        return <<<'TXT'


FAQ REFERENCE (authoritative in-app guidance):
- What is BruDMS? BruDMS is a drainage and sewage reporting platform for residents to report issues and track resolution.
- Who can use BruDMS? Residents in the Brunei service area.
- Is it free? Yes, free for residents.
- Where does it operate? Brunei service area for drainage/sewage reporting.

Account & Profile FAQ:
- Create account: Sign Up -> enter details -> Create account -> verify if prompted.
- Reset password: Use Forgot password on login.
- Update profile: profile photo -> General -> Edit Profile (name, phone, photo, email if available).
- Delete account: settings/profile delete option, or contact support.

Reporting FAQ:
- How to report: Home -> + Add report -> choose problem type -> add photo -> set location -> confirm details -> submit.
- Photo attachment: yes, on camera/photo step.
- Report received confirmation: shown after submit; user can check History for status.
- Anonymous reporting: General -> Preference -> Anonymous Report.
- Processing time: varies; check History (pending/in progress/resolved).

Ziqah / App usage FAQ:
- What is Ziqah? AI assistant for reporting help and app questions.
- View live map: Home -> View Live Map.
- Change preferences: General -> Preference (appearance, language, anonymous report).

FAQ USAGE RULES:
- Prefer FAQ guidance first when user asks app/how-to questions.
- If a question matches FAQ, answer directly and concise.
- If user asks something not covered by FAQ, say what is known and suggest Contact Support.
- Do not invent policies or unsupported steps.
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
