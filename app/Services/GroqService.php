<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = config('services.groq.api_key', '');
        $this->baseUrl = rtrim(config('services.groq.base_uri', 'https://api.groq.com/openai/v1'), '/');
    }

    /**
     * Send a chat completion request to Groq (OpenAI-compatible).
     */
    public function chat(array $messages, array $params = []): array
    {
        $payload = array_merge([
            'model'       => 'llama-3.1-8b-instant',
            'messages'    => $messages,
            'temperature' => 0.4,
            'max_tokens'  => 2048,
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

            Log::error('Groq API Error', ['status' => $status, 'body' => $body]);

            $msg = match (true) {
                $status === 401 => 'Invalid Groq API key. Please check your configuration.',
                $status === 429 => 'Groq API rate limit reached. Please wait a moment and try again.',
                $status >= 500  => 'Groq servers are temporarily unavailable. Please try again later.',
                default         => 'Groq API request failed (HTTP ' . $status . ').',
            };

            throw new \RuntimeException($msg);
        }

        return $response->json();
    }

    /**
     * Get a knowledge-focused answer for the Curevia chatbot.
     * Returns array: ['answer' => string (markdown), 'summary' => string|null, 'suggestions' => string[]]
     */
    public function askCurevia(string $question, array $history = []): array
    {
        $systemPrompt = <<<'PROMPT'
You are **Curevia AI** — the intelligent assistant for Curevia, The Ocean of Knowledge, powered by Llama 3.1.

ANSWER ONLY these topics: science, physics, chemistry, biology, astronomy, space, history, ancient civilizations, geography, animals, wildlife, the human body, medicine, mythology, nature, climate, oceans, technology breakthroughs, inventions, archaeology, paleontology, evolution, mathematics (conceptual), and general encyclopedic knowledge.

DECLINE politely for: programming, coding, software, business advice, creative writing tasks, relationship advice, or anything outside factual knowledge.

You MUST respond with ONLY a JSON object — no text before or after it, no markdown fences:
{"answer": "your markdown answer here", "summary": "- **Key**: fact\n- **Key**: fact\n- **Key**: fact", "suggestions": ["topic 1", "topic 2", "topic 3"]}

- answer: 150-350 words in Markdown with headings and bullet points
- summary: 3-4 bullet points, each max 15 words, format "- **Label**: value"
- suggestions: 3-4 follow-up topic strings, max 55 chars each

If the topic is forbidden, respond: {"answer": "I'm here to explore the wonders of knowledge — science, space, history, and nature! That topic is outside my area. Try asking about black holes, ancient Egypt, or the human brain! 🌍✨", "suggestions": ["How do black holes form?", "What happened in ancient Egypt?", "How does the human brain work?", "What is the Big Bang?"]}
PROMPT;

        $messages = [['role' => 'system', 'content' => $systemPrompt]];

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
            'temperature'     => 0.2,
            'max_tokens'      => 2048,
            'response_format' => ['type' => 'json_object'],
        ]);

        $raw = $result['choices'][0]['message']['content'] ?? '';

        // Strip markdown fencing if the model wrapped the JSON
        $clean = trim($raw);
        if (preg_match('/^```(?:json)?\s*\n?(.+?)\n?\s*```$/s', $clean, $m)) {
            $clean = trim($m[1]);
        }
        $clean = preg_replace('/^```(?:json)?\s*/i', '', $clean);
        $clean = preg_replace('/\s*```$/', '', $clean);

        $parsed = json_decode($clean, true);

        // Secondary attempt: extract JSON object from surrounding text
        if (!$parsed && preg_match('/\{[\s\S]*"answer"[\s\S]*\}/s', $clean, $jm)) {
            $parsed = json_decode($jm[0], true);
        }

        // Tertiary attempt: regex-extract individual field values
        if (!$parsed) {
            $answer = null;
            if (preg_match('/"answer"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/s', $clean, $am)) {
                $answer = json_decode('"' . $am[1] . '"') ?? str_replace(
                    ['\\n', '\\t', '\\"', '\\\\'],
                    ["\n",  "\t",  '"',   '\\'],
                    $am[1]
                );
            }
            if ($answer !== null) {
                $suggestions = [];
                if (preg_match('/"suggestions"\s*:\s*\[([^\]]*?)\]/s', $clean, $sm)) {
                    preg_match_all('/"((?:[^"\\\\]|\\\\.)*?)"/s', $sm[1], $stm);
                    $suggestions = array_slice($stm[1] ?? [], 0, 4);
                }
                $summary = null;
                if (preg_match('/"summary"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/s', $clean, $summ)) {
                    $summary = json_decode('"' . $summ[1] . '"') ?? $summ[1];
                }
                $parsed = ['answer' => $answer, 'summary' => $summary, 'suggestions' => $suggestions];
            }
        }

        if ($parsed && isset($parsed['answer'])) {
            return [
                'answer'      => $parsed['answer'],
                'summary'     => $parsed['summary'] ?? null,
                'suggestions' => array_slice(array_filter((array)($parsed['suggestions'] ?? []), 'is_string'), 0, 4),
            ];
        }

        // Fallback: treat entire raw text as answer
        return [
            'answer'      => $raw ?: 'Sorry, I could not generate a response.',
            'summary'     => null,
            'suggestions' => [],
        ];
    }
}
