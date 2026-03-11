<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepSeekService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = config('services.deepseek.api_key', '');
        $this->baseUrl = rtrim(config('services.deepseek.base_uri', 'https://api.deepseek.com'), '/');
    }

    /**
     * Send a chat completion request to DeepSeek.
     */
    public function chat(array $messages, array $params = []): array
    {
        $payload = array_merge([
            'model'       => 'deepseek-chat',
            'messages'    => $messages,
            'temperature' => 0.4,
            'max_tokens'  => 2048,
            'top_p'       => 0.9,
            'stream'      => false,
        ], $params);

        $response = Http::timeout(60)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])
            ->post($this->baseUrl . '/chat/completions', $payload);

        if ($response->failed()) {
            $status = $response->status();
            $body   = $response->body();

            Log::error('DeepSeek API Error', [
                'status' => $status,
                'body'   => $body,
            ]);

            $msg = match (true) {
                $status === 401 => 'Invalid API key. Please check your DeepSeek configuration.',
                $status === 402 => 'DeepSeek API credits exhausted. Please top up your account at platform.deepseek.com.',
                $status === 429 => 'Too many requests. Please wait a moment and try again.',
                $status >= 500  => 'DeepSeek servers are temporarily unavailable. Please try again later.',
                default         => 'DeepSeek API request failed (HTTP ' . $status . ').',
            };

            throw new \RuntimeException($msg);
        }

        return $response->json();
    }

    /**
     * Get a knowledge-focused answer for the Curevia chatbot.
     */
    public function askCurevia(string $question, array $history = []): string
    {
        $systemPrompt = <<<'PROMPT'
You are **Curevia AI** — the intelligent assistant for Curevia, The Ocean of Knowledge.

Your role:
- Provide accurate, well-structured, factual answers about science, space, history, geography, animals, the human body, mythology, civilizations, technology, nature, and general knowledge.
- Format your responses in clean Markdown with headings, bullet points, and bold text when useful.
- Keep answers concise but comprehensive — aim for 150-400 words unless the user asks for more detail.
- If you don't know something, say so honestly.
- Be friendly, enthusiastic about knowledge, and encourage curiosity.
- When relevant, suggest related topics the user might enjoy exploring.

Never generate harmful, hateful, or misleading content.
PROMPT;

        $messages   = [];
        $messages[] = ['role' => 'system', 'content' => $systemPrompt];

        // Append conversation history (keep last 10 exchanges max)
        $recent = array_slice($history, -20);
        foreach ($recent as $msg) {
            if (isset($msg['role'], $msg['content'])) {
                $messages[] = [
                    'role'    => $msg['role'] === 'user' ? 'user' : 'assistant',
                    'content' => $msg['content'],
                ];
            }
        }

        $messages[] = ['role' => 'user', 'content' => $question];

        $result = $this->chat($messages, [
            'temperature' => 0.35,
            'max_tokens'  => 2048,
        ]);

        return $result['choices'][0]['message']['content'] ?? 'Sorry, I could not generate a response.';
    }
}
