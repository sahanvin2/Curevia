<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Story;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->input('q', '');

        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $articles = Article::where('status', 'published')
            ->where(function ($query) use ($q) {
                $query->where('title', 'ilike', "%{$q}%")
                      ->orWhere('summary', 'ilike', "%{$q}%");
            })
            ->take(5)
            ->get(['title', 'slug', 'summary', 'featured_image', 'read_time'])
            ->map(fn($a) => ['type' => 'article', 'title' => $a->title, 'url' => route('encyclopedia.show', $a->slug), 'desc' => \Illuminate\Support\Str::limit($a->summary, 80), 'img' => $a->featured_image, 'meta' => $a->read_time . ' min read']);

        $stories = Story::where('status', 'published')
            ->where(function ($query) use ($q) {
                $query->where('title', 'ilike', "%{$q}%")
                      ->orWhere('excerpt', 'ilike', "%{$q}%");
            })
            ->take(3)
            ->get(['title', 'slug', 'excerpt', 'featured_image', 'read_time'])
            ->map(fn($s) => ['type' => 'story', 'title' => $s->title, 'url' => route('stories.show', $s->slug), 'desc' => \Illuminate\Support\Str::limit($s->excerpt, 80), 'img' => $s->featured_image, 'meta' => $s->read_time . ' min read']);

        $products = Product::where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('name', 'ilike', "%{$q}%")
                      ->orWhere('description', 'ilike', "%{$q}%");
            })
            ->take(3)
            ->get(['name', 'slug', 'description', 'image', 'price'])
            ->map(fn($p) => ['type' => 'product', 'title' => $p->name, 'url' => route('shop.show', $p->slug), 'desc' => \Illuminate\Support\Str::limit($p->description, 80), 'img' => $p->image, 'meta' => '$' . number_format($p->price, 2)]);

        $results = $articles->concat($stories)->concat($products);

        return response()->json(['results' => $results->values()]);
    }
}
