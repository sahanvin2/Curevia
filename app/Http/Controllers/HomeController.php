<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Story;
use App\Models\Product;
use App\Models\Category;
use App\Models\ContributorProfile;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $articles = Article::with('category')->where('status', 'published')->orderByDesc('views')->take(12)->get();
        $stories = Story::with(['category', 'author'])->where('status', 'published')->orderByDesc('published_at')->take(4)->get();
        $products = Product::where('is_active', true)->orderByDesc('rating')->take(4)->get();
        $categories = Category::orderBy('sort_order')->get();
        $contributors = ContributorProfile::with('user')->orderByDesc('reputation')->take(4)->get();

        return view('home', compact('articles', 'stories', 'products', 'categories', 'contributors'));
    }
}
