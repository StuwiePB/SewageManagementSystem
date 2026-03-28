<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'message' => ['nullable', 'string', 'max:4000'],
            'image' => ['nullable', 'string'], // base64 data URL
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
            // Expect data URL like data:image/png;base64,...
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
        $systemPrompt = <<<'TXT'
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

        $response = Http::withToken($apiKey)
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $content],
                ],
                'max_tokens' => 1024,
            ]);

        if (! $response->successful()) {
            Log::error('OpenAI API error', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return response()->json([
                'error' => 'AI service temporarily unavailable. Please try again.',
            ], 502);
        }

        $data = $response->json();
        $text = $data['choices'][0]['message']['content'] ?? '';

        return response()->json(['reply' => trim($text)]);
    }
}
