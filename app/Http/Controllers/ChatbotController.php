<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Story;
use App\Models\Category;
use App\Services\GeminiService;
use App\Services\GroqService;
use App\Services\DeepSeekService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    public function index()
    {
        return view('chat');
    }

    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'model'   => 'nullable|string|in:gemini,groq,deepseek',
            'history' => 'nullable|array|max:20',
            'history.*.role'    => 'required_with:history|string|in:user,assistant',
            'history.*.content' => 'required_with:history|string|max:5000',
        ]);

        $model   = $request->input('model', 'gemini');
        $message = $request->input('message');
        $history = $request->input('history', []);

        try {
            $result = match ($model) {
                'groq'     => app(GroqService::class)->askCurevia($message, $history),
                'deepseek' => app(DeepSeekService::class)->askCurevia($message, $history),
                default    => app(GeminiService::class)->askCurevia($message, $history),
            };

            return response()->json([
                'success'     => true,
                'answer'      => $result['answer'],
                'summary'     => $result['summary'],
                'suggestions' => $result['suggestions'],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'answer'  => $e->getMessage(),
            ], 503);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'answer'  => 'Sorry, Curevia AI is temporarily unavailable. Please try again in a moment.',
            ], 503);
        }
    }

    /**
     * Expand a chat answer into a rich post using Gemini, then save as Story or Article.
     */
    public function shareAsPost(Request $request, GeminiService $gemini): JsonResponse
    {
        $request->validate([
            'type'        => 'required|string|in:story,encyclopedia',
            'title'       => 'required|string|max:255',
            'content'     => 'required|string|max:10000',
            'category'    => 'nullable|string|max:50',
            'image_url'   => 'nullable|url|max:500',
            'description' => 'nullable|string|max:1000',
            'quick_facts' => 'nullable|array|max:12',
            'quick_facts.*.label' => 'required_with:quick_facts|string|max:80',
            'quick_facts.*.value' => 'required_with:quick_facts|string|max:200',
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'You must be logged in to share posts.'], 401);
        }

        $type        = $request->input('type');
        $title       = $request->input('title');
        $content     = $request->input('content');
        $userCat     = $request->input('category', '');
        $userImage   = $request->input('image_url', '');
        $userDesc    = $request->input('description', '');
        $userFacts   = $request->input('quick_facts', []);

        // Build prompt — tell Gemini which fields the user has already provided
        $alreadyHave = [];
        if ($userCat)   $alreadyHave[] = "category is already set to: {$userCat}";
        if ($userDesc)  $alreadyHave[] = "the description/excerpt is already written: \"{$userDesc}\"";
        if (!empty($userFacts)) $alreadyHave[] = 'quick_facts are already provided — do NOT generate new ones, use null for that field';
        $alreadyNote = empty($alreadyHave) ? '' : '\nNote: ' . implode('; ', $alreadyHave) . '.';

        $expandPrompt = $type === 'encyclopedia'
            ? "You are creating a Curevia encyclopedia article. Based on the following chat content, generate a rich, well-structured encyclopedia article in JSON format with these fields:\n- \"summary\": A 1-2 sentence summary (skip if already provided in notes below)\n- \"content\": The full article in Markdown (at least 300 words, well-structured with headings)\n- \"quick_facts\": An array of 4-6 quick facts, each as {\"label\": \"...\", \"value\": \"...\"} (skip if told in notes)\n- \"category\": One of: Space, Earth, Science, History, Animals, Human Body, Countries, Nature, Mythology, Civilizations, Technology (skip if told in notes){$alreadyNote}\n\nChat content to expand:\nTitle: {$title}\n{$content}\n\nRespond ONLY with valid JSON, no markdown fencing."
            : "You are creating a Curevia story article. Based on the following chat content, generate a rich, well-structured story in JSON format with these fields:\n- \"excerpt\": A compelling 1-2 sentence excerpt (skip if already provided in notes below)\n- \"content\": The full story in Markdown (at least 300 words, engaging narrative style with headings)\n- \"category\": One of: Space, Earth, Science, History, Animals, Human Body, Countries, Nature, Mythology, Civilizations, Technology (skip if told in notes){$alreadyNote}\n\nChat content to expand:\nTitle: {$title}\n{$content}\n\nRespond ONLY with valid JSON, no markdown fencing.";

        try {
            $expanded = $gemini->chat(
                [['role' => 'user', 'parts' => [['text' => $expandPrompt]]]],
                ['temperature' => 0.5, 'maxOutputTokens' => 4096]
            );

            $raw = $expanded['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $raw = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
            $raw = preg_replace('/\s*```$/', '', $raw);
            $data = json_decode($raw, true);

            if (!$data) {
                return response()->json(['success' => false, 'message' => 'Failed to generate post content. Please try again.'], 422);
            }

            // User values take priority over AI-generated values
            $catName = $userCat ?: ($data['category'] ?? 'Science');
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($catName)],
                ['name' => $catName, 'description' => $catName . ' articles']
            );

            $slug = Str::slug($title);
            $baseSlug = $slug;
            $counter = 1;
            while (($type === 'encyclopedia' ? Article::where('slug', $slug)->exists() : Story::where('slug', $slug)->exists())) {
                $slug = $baseSlug . '-' . $counter++;
            }

            if ($type === 'encyclopedia') {
                $finalContent = $data['content'] ?? $content;
                $article = Article::create([
                    'title'          => $title,
                    'slug'           => $slug,
                    'summary'        => $userDesc ?: ($data['summary'] ?? Str::limit(strip_tags($finalContent), 200)),
                    'content'        => $finalContent,
                    'featured_image' => $userImage ?: null,
                    'category_id'    => $category->id,
                    'author_id'      => $user->id,
                    'status'         => 'published',
                    'read_time'      => max(3, (int) ceil(str_word_count(strip_tags($finalContent)) / 200)),
                    'quick_facts'    => !empty($userFacts) ? $userFacts : ($data['quick_facts'] ?? null),
                    'published_at'   => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Encyclopedia article published!',
                    'url'     => route('encyclopedia.show', $article->slug),
                ]);
            } else {
                $finalContent = $data['content'] ?? $content;
                $story = Story::create([
                    'title'          => $title,
                    'slug'           => $slug,
                    'excerpt'        => $userDesc ?: ($data['excerpt'] ?? Str::limit(strip_tags($finalContent), 200)),
                    'content'        => $finalContent,
                    'featured_image' => $userImage ?: null,
                    'category_id'    => $category->id,
                    'author_id'      => $user->id,
                    'status'         => 'published',
                    'read_time'      => max(3, (int) ceil(str_word_count(strip_tags($finalContent)) / 200)),
                    'published_at'   => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Story published!',
                    'url'     => route('stories.show', $story->slug),
                ]);
            }
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Failed to create post: ' . $e->getMessage()], 500);
        }
    }
}
