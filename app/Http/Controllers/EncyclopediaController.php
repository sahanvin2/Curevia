<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class EncyclopediaController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::with('category')->where('status', 'published');

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                  ->orWhere('summary', 'ilike', "%{$search}%")
                  ->orWhere('content', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->input('category')));
        }

        $articles = $query->orderByDesc('views')->paginate(6);
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

        $related = Article::with('category')
            ->where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->where('status', 'published')
            ->orderByDesc('views')
            ->take(4)
            ->get();

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

        $relatedProducts = Product::whereIn('category', $productCategories)
            ->where('is_active', true)
            ->orderByDesc('reviews_count')
            ->take(4)
            ->get();

        if ($relatedProducts->count() < 2) {
            $relatedProducts = Product::where('is_active', true)
                ->orderByDesc('rating')
                ->take(4)
                ->get();
        }

        return view('encyclopedia.show', compact('article', 'related', 'relatedProducts'));
    }
}
