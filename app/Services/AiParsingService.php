<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiParsingService
{
    protected $apiKey;
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
    }

    public function parse($text)
    {
        $prompt = <<<EOD
You are a gym and nutrition tracking assistant. Your task is to parse user input into a precise JSON object.
The input can be in English or Hebrew.

Output format MUST be valid JSON:
{
    "type": "workout" or "meal",
    "data": {
        "exercise": "string (translated to English)",
        "weight": float,
        "reps": integer,
        "sets": integer,
        "description": "string (translated to English)",
        "calories": float,
        "protein": float,
        "carbs": float,
        "fats": float
    }
}

Rules:
1. If the input is about physical exercise/gym, type is "workout".
2. If the input is about eating/food, type is "meal".
3. ALWAYS translate the exercise name or food description to English.
4. For meals: If the user DOES NOT provide calories, protein, carbs, or fats, you MUST ESTIMATE them based on the description (approximate values for typical portions).
5. If a workout value is missing, use null.

Input text: "$text"
EOD;

        try {
            $response = Http::withHeaders([
                'x-goog-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'response_mime_type' => 'application/json',
                ]
            ]);

            if ($response->successful()) {
                $json = $response->json();
                $content = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                
                Log::info('Gemini Raw Response: ' . $content);

                // Better cleaning of the response
                $content = trim($content);
                // Remove Markdown code blocks if present
                if (preg_match('/^```(?:json)?\s*(.*?)\s*```$/s', $content, $matches)) {
                    $content = $matches[1];
                }

                $result = json_decode($content, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('JSON Decode Error: ' . json_last_error_msg() . ' | Content: ' . $content);
                    return null;
                }

                return $result;
            }

            Log::error('Gemini API Error: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('AiParsingService Exception: ' . $e->getMessage());
        }

        return null;
    }
}
