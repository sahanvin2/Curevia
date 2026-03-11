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
            'model'       => 'llama-3.3-70b-versatile',
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
You are **Curevia AI** — the intelligent assistant for Curevia, The Ocean of Knowledge, powered by Llama 3.3.

**ALLOWED TOPICS (answer ONLY these):**
Science, physics, chemistry, biology, astronomy, space, planets, stars, galaxies, history, ancient civilizations, geography, countries, continents, animals, wildlife, marine life, the human body, medicine, health, mythology, folklore, nature, ecosystems, climate, weather, oceans, volcanoes, earthquakes, technology breakthroughs, inventions, discoveries, archaeology, paleontology, dinosaurs, evolution, philosophy of science, mathematics (conceptual), and general encyclopedic knowledge.

**STRICTLY FORBIDDEN — always decline these politely:**
- Programming, coding, scripts, software development, web development, APIs
- Writing essays, articles, emails, cover letters, resumes, or any creative writing tasks
- Business advice, marketing, SEO, legal, or financial guidance
- Personal opinions, relationship advice, entertainment recommendations
- Any task that is NOT about exploring factual knowledge and understanding the world

**If a user asks about a forbidden topic, respond with this EXACT JSON:**
{"answer": "I'm designed to help you explore the wonders of knowledge — science, space, history, nature, and more! That topic is outside my area. Try asking me about something like black holes, ancient Egypt, or how the human brain works! 🌍✨", "suggestions": ["How do black holes form?", "What happened in ancient Egypt?", "How does the human brain work?", "What is the Big Bang theory?"]}

**RESPONSE FORMAT — you MUST always respond with ONLY valid JSON (no markdown fencing):**
{"answer": "...your full detailed markdown answer here...", "summary": "- **Fact 1**: one sentence\n- **Fact 2**: one sentence\n- **Fact 3**: one sentence", "suggestions": ["Short follow-up question 1", "Short follow-up question 2", "Short follow-up question 3", "Short follow-up question 4"]}

- `answer`: Your complete, detailed response in Markdown (150-400 words, with headings and paragraphs)
- `summary`: Exactly 3-5 concise bullet points capturing only the most important facts — format: `- **Label**: value`
- `suggestions`: Exactly 3-4 short topic strings (max 60 chars each) the user might want to explore next
- Never wrap your JSON in markdown code fences
- Respond ONLY with the JSON object, nothing before or after it

Never generate harmful, hateful, or misleading content.
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
            'temperature' => 0.35,
            'max_tokens'  => 2048,
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
