<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class EncyclopediaController extends Controller
{
    private function applyStableOrdering(Builder $query): Builder
    {
        return $query
            ->orderByDesc('views')
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    public function index(Request $request)
    {
        $query = Article::with('category')->where('status', 'published');

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->input('category')));
        }

        $articles = $this->applyStableOrdering($query)->paginate(6);
        $categories = Category::orderBy('sort_order')->get();

        if ($request->ajax()) {
            $html = '';
            foreach ($articles as $a) {
                $html .= view('encyclopedia._card', compact('a'))->render();
            }
            return response()->json(['html' => $html, 'next' => $articles->nextPageUrl()]);
        }

        return view('encyclopedia.index', compact('articles', 'categories'));
    }

    public function show(string $slug)
    {
        $article = Article::with(['category', 'author'])->where('slug', $slug)->where('status', 'published')->firstOrFail();

        $related = Cache::remember(
            "article:{$article->id}:related.v1",
            now()->addMinutes(10),
            function () use ($article) {
                return Article::with('category')
                    ->where('category_id', $article->category_id)
                    ->where('id', '!=', $article->id)
                    ->where('status', 'published')
                    ->orderByDesc('views')
                    ->orderByDesc('published_at')
                    ->orderByDesc('id')
                    ->take(4)
                    ->get();
            }
        );

        // Map article categories to relevant product categories
        $categoryProductMap = [
            'Space'        => ['Space', 'Astronomy', 'Books'],
            'Earth'        => ['Nature', 'Books'],
            'Science'      => ['Science', 'Books'],
            'History'      => ['History', 'Books'],
            'Animals'      => ['Nature', 'Books'],
            'Human Body'   => ['Science', 'Books'],
            'Countries'    => ['History', 'Books'],
            'Nature'       => ['Nature', 'Books'],
            'Mythology'    => ['History', 'Books'],
            'Zodiac'       => ['Space', 'Astronomy', 'Books'],
            'Civilizations'=> ['History', 'Books'],
            'Technology'   => ['Technology', 'Science', 'Books'],
        ];
        $articleCategoryName = $article->category->name ?? 'Science';
        $productCategories   = $categoryProductMap[$articleCategoryName] ?? ['Books'];

        $relatedProducts = Cache::remember(
            'article-category-products:' . md5(implode('|', $productCategories)) . '.v1',
            now()->addMinutes(10),
            function () use ($productCategories) {
                return Product::whereIn('category', $productCategories)
                    ->where('is_active', true)
                    ->orderByDesc('reviews_count')
                    ->take(4)
                    ->get();
            }
        );

        if ($relatedProducts->count() < 2) {
            $relatedProducts = Cache::remember(
                'article-products:fallback.v1',
                now()->addMinutes(10),
                function () {
                    return Product::where('is_active', true)
                        ->orderByDesc('rating')
                        ->take(4)
                        ->get();
                }
            );
        }

        return view('encyclopedia.show', compact('article', 'related', 'relatedProducts'));
    }
}
