<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Models\Product;
use App\Models\Article;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $stories = Story::where('status', 'published')
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at']);

        $products = Product::where('is_active', true)
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at']);

        $articles = Article::where('status', 'published')
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at']);

        $xml = view('sitemap', compact('stories', 'products', 'articles'))->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }
}
