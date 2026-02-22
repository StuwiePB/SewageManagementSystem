<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiChatController extends Controller
{
    public function chat(Request $request)
    {
        $validated = $request->validate(['message' => 'required|string|max:500']);
        $userMessage = trim($validated['message']);
        $reply = null;

        $apiKey = config('services.openai.api_key');

        if ($apiKey) {
            try {
                $isOpenAI = str_starts_with($apiKey, 'sk-');
                $isZai = strpos($apiKey, '.') !== false && !str_starts_with($apiKey, 'sk-');

                if ($isOpenAI) {
                    $reply = $this->callOpenAI($apiKey, $userMessage);
                } elseif ($isZai) {
                    $reply = $this->callZai($apiKey, $userMessage);
                }
            } catch (\Throwable $e) {
                \Log::warning('AI API failed, using fallback', ['error' => $e->getMessage()]);
            }
        }

        if ($reply === null || $reply === '') {
            $reply = $this->fallbackResponse($userMessage);
        }

        return response()->json(['reply' => $reply]);
    }

    private function callOpenAI(string $apiKey, string $userMessage): ?string
    {
        $model = config('services.openai.model', 'gpt-4o-mini');
        $response = Http::timeout(15)->withToken($apiKey)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt()],
                    ['role' => 'user', 'content' => $userMessage],
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException($response->json('error.message') ?? 'API request failed');
        }

        $data = $response->json();
        return $data['choices'][0]['message']['content'] ?? null;
    }

    private function callZai(string $apiKey, string $userMessage): ?string
    {
        $model = config('services.openai.model', 'glm-4-7');
        $endpoint = config('services.openai.zai_endpoint');

        $response = Http::timeout(15)->withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post($endpoint, [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $this->systemPrompt()],
                ['role' => 'user', 'content' => $userMessage],
            ],
            'temperature' => 0.7,
            'stream' => false,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException($response->json('error.message') ?? 'API request failed');
        }

        $data = $response->json();
        return $data['choices'][0]['message']['content'] ?? null;
    }

    private function systemPrompt(): string
    {
        return "You are a helpful AI assistant for a municipal sewage issue reporting portal in Brunei Darussalam. Answer questions about reporting sewage issues, checking status, and emergency contact. Emergency Hotline: (555) 123-EMER (3637) or (673) 8377102 - 24/7. Office Hours: Mon-Fri 8AM-5PM. Be brief and helpful.";
    }

    private function fallbackResponse(string $msg): string
    {
        $m = strtolower($msg);

        if (preg_match('/\b(emergency|urgent|help|hotline|phone|number|call|contact)\b/i', $m)) {
            return "For sewage emergencies, call (555) 123-EMER (3637) or (673) 8377102 – available 24/7. For urgent health or environmental risks, call immediately.";
        }

        if (preg_match('/\b(how do i report|how to report|report an? issue|submit report)\b/i', $m)) {
            return "To report: 1) Click 'Report a New Issue' on this page 2) Fill in issue type, severity, description 3) Enter location (district, mukim, address) 4) Optionally add a photo 5) Submit and save your report ID for tracking.";
        }

        if (preg_match('/\b(status|track|check report|report id|reference number)\b/i', $m)) {
            return "Use 'Check Report Status' on this page. Enter your report reference number (e.g. RPT-20260206-XXXXXX) to see progress.";
        }

        if (preg_match('/\b(what to report|what can i report)\b/i', $m)) {
            return "Report: sewage blockages, overflows, strong odors, damaged infrastructure (broken manhole covers, exposed pipes), and illegal dumping into sewers.";
        }

        if (preg_match('/\b(hours|office|when|available)\b/i', $m)) {
            return "Office hours: Mon–Fri 8AM–5PM. Emergency hotline (555) 123-EMER is 24/7 for urgent sewage emergencies.";
        }

        if (preg_match('/\b(hello|hi|hey)\b/i', $m) && strlen($m) < 20) {
            return "Hello! I help with sewage questions in Brunei. Ask about reporting issues, checking status, or emergency contacts.";
        }

        return "I help with sewage reporting in Brunei. You can ask about: reporting an issue (use 'Report a New Issue'), checking status (use your report ID), or emergency contact (555) 123-EMER or (673) 8377102. What would you like to know?";
    }
}
