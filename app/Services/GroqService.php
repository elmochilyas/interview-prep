<?php

namespace App\Services;

use App\Models\Concept;
use App\Models\GeneratedQuestion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqService
{
    private string $apiKey;
    private string $endpoint = 'https://api.groq.com/openai/v1/chat/completions';
    private string $model = 'llama-3.1-8b-instant';

    public function __construct()
    {
        $this->apiKey = config('services.groq.key');
    }

    public function generateInterviewQuestions(Concept $concept): ?GeneratedQuestion
    {
        $prompt = $this->buildPrompt($concept);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(30)->post($this->endpoint, [
                'model'    => $this->model,
                'messages' => [
                    [
                        'role'    => 'system',
                        'content' => 'You are a technical interview coach. Always respond with a valid JSON array of exactly 5 strings. No explanation, no markdown, just the JSON array.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.7,
                'max_tokens'  => 800,
            ]);

            if (!$response->successful()) {
                Log::error('Groq API error', [
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                    'concept' => $concept->id,
                ]);
                return null;
            }

            $content = $response->json('choices.0.message.content');
            $questions = json_decode($content, true);

            if (!is_array($questions) || count($questions) !== 5) {
                Log::error('Groq returned invalid format', [
                    'content' => $content,
                    'concept' => $concept->id,
                ]);
                return null;
            }

            return GeneratedQuestion::create([
                'concept_id' => $concept->id,
                'questions'  => $questions,
            ]);

        } catch (\Exception $e) {
            Log::error('Groq API exception', [
                'message' => $e->getMessage(),
                'concept' => $concept->id,
            ]);
            return null;
        }
    }

    private function buildPrompt(Concept $concept): string
    {
        return <<<EOT
Generate exactly 5 technical interview questions about the following concept.

Concept title: {$concept->title}
Concept explanation: {$concept->explanation}
Difficulty level: {$concept->difficulty}

Return ONLY a JSON array of 5 question strings.
Example format: ["Question 1?", "Question 2?", "Question 3?", "Question 4?", "Question 5?"]
No explanation, no markdown code blocks, just the raw JSON array.
EOT;
    }
}