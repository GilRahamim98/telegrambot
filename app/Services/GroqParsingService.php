<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqParsingService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.groq.key');
    }

    public function parse($text)
    {
        $prompt = <<<EOD
Parse the following text into a structured JSON object for a gym/meal tracking app.
The text could be a workout or a meal.

Output format:
{
    "type": "workout" | "meal",
    "data": {
        "exercise": string, "weight": float, "reps": integer, "sets": integer,
        "description": string, "calories": float, "protein": float, "carbs": float, "fats": float
    }
}
Rules: Return ONLY the JSON object. Use null for missing values.
Text: "$text"
EOD;

        try {
            $response = Http::withToken($this->apiKey)->post($this->baseUrl, [
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'response_format' => ['type' => 'json_object']
            ]);

            if ($response->successful()) {
                return json_decode($response->json()['choices'][0]['message']['content'], true);
            }
            Log::error('Groq API Error: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('GroqParsingService Exception: ' . $e->getMessage());
        }
        return null;
    }
}
