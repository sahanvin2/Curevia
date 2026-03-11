<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Story::with(['category', 'author'])->where('status', 'published');

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                  ->orWhere('excerpt', 'ilike', "%{$search}%")
                  ->orWhere('content', 'ilike', "%{$search}%");
            });
        }

        $featured = Story::with(['category', 'author'])->where('status', 'published')->where('is_featured', true)->orderByDesc('published_at')->first();
        $stories = $query->orderByDesc('published_at')->paginate(6);
        $categories = Category::orderBy('sort_order')->get();

        if ($request->ajax()) {
            $html = '';
            foreach ($stories as $story) {
                $html .= view('stories._card', compact('story'))->render();
            }
            return response()->json(['html' => $html, 'next' => $stories->nextPageUrl()]);
        }

        return view('stories.index', compact('stories', 'featured', 'categories'));
    }

    public function show(string $slug)
    {
        $story = Story::with(['category', 'author'])->where('slug', $slug)->where('status', 'published')->firstOrFail();

        $related = Story::with('category')
            ->where('category_id', $story->category_id)
            ->where('id', '!=', $story->id)
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        // Build keyword list from story title + category name for product matching
        $stopwords = ['with','from','that','this','have','will','your','what','about','does',
                      'into','been','more','than','when','they','their','there','creates',
                      'create','which','where','some','also','other','after','brain','mind'];
        $rawText = strtolower($story->title . ' ' . ($story->category->name ?? ''));
        $keywords = collect(preg_split('/\W+/', $rawText))
            ->filter(fn($w) => strlen($w) >= 4)
            ->filter(fn($w) => !in_array($w, $stopwords))
            ->unique()
            ->values()
            ->take(6);

        $relatedProducts = collect();

        if ($keywords->isNotEmpty()) {
            $relatedProducts = Product::where('is_active', true)
                ->where(function ($q) use ($keywords) {
                    foreach ($keywords as $kw) {
                        $q->orWhere('name', 'ilike', "%{$kw}%")
                          ->orWhere('description', 'ilike', "%{$kw}%")
                          ->orWhere('category', 'ilike', "%{$kw}%");
                    }
                })
                ->orderByDesc('rating')
                ->take(4)
                ->get();
        }

        // Fallback: top-rated products if no keyword match
        if ($relatedProducts->isEmpty()) {
            $relatedProducts = Product::where('is_active', true)
                ->orderByDesc('rating')
                ->take(4)
                ->get();
        }

        return view('stories.show', compact('story', 'related', 'relatedProducts'));
    }
}
