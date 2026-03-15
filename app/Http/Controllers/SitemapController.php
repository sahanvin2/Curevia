<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Models\Product;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('sitemap.xml.v3', now()->addMinutes(5), function () {
            $stories = Story::where('status', 'published')
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->get(['slug', 'updated_at']);

            $products = Product::where('is_active', true)
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->get(['slug', 'updated_at']);

            $articles = Article::where('status', 'published')
                ->whereNotNull('slug')
                ->where('slug', '!=', '')
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->get(['slug', 'updated_at']);

            $categories = Category::query()
                ->whereNotNull('slug')
                ->where('slug', '!=', '')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['slug', 'updated_at']);

            return view('sitemap', compact('stories', 'products', 'articles', 'categories'))->render();
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
