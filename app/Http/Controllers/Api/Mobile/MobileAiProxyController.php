<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MobileAiProxyController extends Controller
{
    /**
     * Proxy intelligent voice assistant queries to Gemini AI.
     * Keeps API keys secured on the backend server.
     */
    public function ask(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string|max:500',
            'network_context' => 'nullable|string|max:1000',
        ]);

        $prompt = trim($request->input('prompt'));
        $context = $request->input('network_context', '');
        $apiKey = config('services.gemini.api_key', env('GEMINI_API_KEY', ''));

        if (empty($apiKey)) {
            // Smart local fallback response if server key is not yet configured
            $fallback = $this->generateLocalFallbackAnswer($prompt);
            return response()->json([
                'success' => true,
                'answer' => $fallback,
            ]);
        }

        $models = [
            'gemini-2.0-flash',
            'gemini-1.5-flash',
            'gemini-flash-latest',
        ];

        $systemInstruction = "Kamu adalah asisten AI cerdas untuk teknisi ISP dan ahli jaringan TR-069 GenieACS. " .
            "Jawablah pertanyaan secara ringkas, lugas, ramah, dan solutif dalam Bahasa Indonesia (maksimal 2 hingga 3 kalimat). " .
            "Hindari format markdown seperti bintang atau bullet point agar nyaman saat dibacakan dengan Text-To-Speech.";

        $fullPrompt = "{$systemInstruction}\n[Kondisi Jaringan]\n{$context}\nPertanyaan teknisi:\n{$prompt}";

        foreach ($models as $model) {
            try {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

                $response = Http::timeout(8)
                    ->asJson()
                    ->post($url, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $fullPrompt]
                                ]
                            ]
                        ]
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $rawText = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    if (!empty($rawText)) {
                        $cleanText = trim(preg_replace('/[*#_`~>|\[\]()]/', ' ', $rawText));
                        $cleanText = preg_replace('/\s+/', ' ', $cleanText);
                        return response()->json([
                            'success' => true,
                            'answer' => $cleanText,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Mobile AI Proxy attempt for model {$model} failed: " . $e->getMessage());
            }
        }

        // Fallback if AI service is temporarily unavailable
        return response()->json([
            'success' => true,
            'answer' => $this->generateLocalFallbackAnswer($prompt),
        ]);
    }

    private function generateLocalFallbackAnswer(string $prompt): string
    {
        $lower = strtolower($prompt);
        if (str_contains($lower, 'redaman') || str_contains($lower, 'sinyal') || str_contains($lower, 'rx')) {
            return "Nilai redaman standar optik ONT berada pada kisaran minus 18 sampai minus 24 dBm. Jika redaman melebihi minus 27 dBm, periksa kebersihan patchcord dan konektor ODP.";
        }
        if (str_contains($lower, 'reboot') || str_contains($lower, 'restart')) {
            return "Anda dapat menekan tombol Restart Modem di menu aksi cepat atau meminta NOC untuk melakukan reboot via sistem ACS.";
        }
        if (str_contains($lower, 'wifi') || str_contains($lower, 'sandi') || str_contains($lower, 'password')) {
            return "Perubahan kata sandi Wi-Fi dual-band dapat dilakukan langsung melalui tab Pengaturan WiFi pada detail perangkat.";
        }

        return "Perintah Anda telah diterima oleh asisten dsBilling Staff. Silakan gunakan menu tindakan cepat untuk eksekusi pada modem.";
    }
}
