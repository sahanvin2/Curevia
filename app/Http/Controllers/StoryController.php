<?php

namespace App\Http\Controllers;

use App\Models\Story;
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

        return view('stories.show', compact('story', 'related'));
    }
}
