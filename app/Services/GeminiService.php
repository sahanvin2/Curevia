<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = config('services.gemini.api_key', '');
        $this->baseUrl = rtrim(config('services.gemini.base_uri', 'https://generativelanguage.googleapis.com'), '/');
    }

    /**
     * Send a generateContent request to Gemini.
     */
    public function chat(array $contents, array $params = []): array
    {
        $model = $params['model'] ?? 'gemini-2.5-flash';
        unset($params['model']);

        $payload = [
            'contents'         => $contents,
            'generationConfig' => array_merge([
                'temperature'     => 0.4,
                'maxOutputTokens' => 2048,
                'topP'            => 0.9,
            ], $params),
        ];

        // Include system instruction if provided
        if (!empty($params['systemInstruction'])) {
            $payload['systemInstruction'] = [
                'parts' => [['text' => $params['systemInstruction']]],
            ];
            unset($payload['generationConfig']['systemInstruction']);
        }

        $url = $this->baseUrl . '/v1beta/models/' . $model . ':generateContent?key=' . $this->apiKey;

        $response = Http::timeout(60)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $payload);

        if ($response->failed()) {
            $status = $response->status();
            $body   = $response->body();

            Log::error('Gemini API Error', [
                'status' => $status,
                'body'   => $body,
            ]);

            $msg = match (true) {
                $status === 400 => 'Invalid request to Gemini API. ' . $this->extractError($body),
                $status === 403 => 'Gemini API key is invalid or not authorized.',
                $status === 429 => 'Too many requests. Please wait a moment and try again.',
                $status >= 500  => 'Gemini servers are temporarily unavailable. Please try again later.',
                default         => 'Gemini API request failed (HTTP ' . $status . ').',
            };

            throw new \RuntimeException($msg);
        }

        return $response->json();
    }

    /**
     * Get a knowledge-focused answer for the Curevia chatbot.
     * Returns array: ['answer' => string (markdown), 'suggestions' => string[]]
     */
    public function askCurevia(string $question, array $history = []): array
    {
        $systemPrompt = <<<'PROMPT'
You are **Curevia AI** — the intelligent assistant for Curevia, The Ocean of Knowledge.

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

**Your style:**
- Provide accurate, well-structured, factual answers
- Format the `answer` field in clean Markdown with headings, bullet points, and bold text
- Keep answers concise but comprehensive — aim for 150-400 words unless the user asks for more detail
- Be friendly, enthusiastic about knowledge, and encourage curiosity
- If you don't know something, say so honestly

**RESPONSE FORMAT — you MUST always respond with ONLY valid JSON (no markdown fencing):**
```
{"answer": "...your full markdown answer here...", "suggestions": ["Short follow-up question or topic 1", "Short follow-up question or topic 2", "Short follow-up question or topic 3", "Short follow-up question or topic 4"]}
```
- `answer`: Your complete response in Markdown
- `suggestions`: Exactly 3-4 short topic strings (max 60 chars each) the user might want to explore next, naturally related to your answer
- Never wrap your JSON in markdown code fences

Never generate harmful, hateful, or misleading content.
PROMPT;

        // Build Gemini conversation format
        $contents = [];

        // Append conversation history (last 20 messages)
        $recent = array_slice($history, -20);
        foreach ($recent as $msg) {
            if (isset($msg['role'], $msg['content'])) {
                $contents[] = [
                    'role'  => $msg['role'] === 'user' ? 'user' : 'model',
                    'parts' => [['text' => $msg['content']]],
                ];
            }
        }

        // Add current question
        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => $question]],
        ];

        $result = $this->chat($contents, [
            'temperature'       => 0.35,
            'maxOutputTokens'   => 2048,
            'systemInstruction' => $systemPrompt,
        ]);

        $raw = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

        // Strip markdown fencing if Gemini ignored the instruction
        $clean = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
        $clean = preg_replace('/\s*```$/', '', $clean);

        $parsed = json_decode($clean, true);
        if ($parsed && isset($parsed['answer'])) {
            return [
                'answer'      => $parsed['answer'],
                'suggestions' => array_slice(array_filter((array)($parsed['suggestions'] ?? []), 'is_string'), 0, 4),
            ];
        }

        // Fallback: treat entire raw text as answer
        return [
            'answer'      => $raw ?: 'Sorry, I could not generate a response.',
            'suggestions' => [],
        ];
    }

    private function extractError(string $body): string
    {
        $data = json_decode($body, true);
        return $data['error']['message'] ?? '';
    }
}
