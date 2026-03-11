<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $bookmarkCount   = Bookmark::where('user_id', $user->id)->count();
        $commentCount    = Comment::where('user_id', $user->id)->count();

        // Fetch bookmarks with their bookmarkable article
        $bookmarks = Bookmark::where('user_id', $user->id)
            ->with(['bookmarkable' => function ($q) {
                $q->with('category');
            }])
            ->latest()
            ->take(6)
            ->get()
            ->filter(fn($b) => $b->bookmarkable !== null);

        return view('dashboard', compact('bookmarkCount', 'commentCount', 'bookmarks'));
    }
}
