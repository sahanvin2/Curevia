<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Story;
use App\Models\Category;
use Illuminate\Http\Request;

class DiscoverController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('articles')->orderBy('sort_order')->get();
        $totalArticles = Article::where('status', 'published')->count();
        $totalStories = Story::where('status', 'published')->count();
        $totalViews = Article::where('status', 'published')->sum('views');

        $randomArticles = Article::with('category')->where('status', 'published')->inRandomOrder()->take(6)->get();
        $topArticles = Article::with('category')->where('status', 'published')->orderByDesc('views')->take(6)->get();

        return view('discover', compact('categories', 'totalArticles', 'totalStories', 'totalViews', 'randomArticles', 'topArticles'));
    }
}
