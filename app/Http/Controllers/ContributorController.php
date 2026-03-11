<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\ContributorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContributorController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        $myArticles        = Article::where('author_id', $user->id)->orderByDesc('created_at')->take(5)->get();
        $totalArticles     = Article::where('author_id', $user->id)->count();
        $publishedArticles = Article::where('author_id', $user->id)->where('status', 'published')->count();
        $draftArticles     = Article::where('author_id', $user->id)->where('status', 'draft')->count();
        $totalViews        = Article::where('author_id', $user->id)->sum('views');
        $profile           = ContributorProfile::firstOrCreate(['user_id' => $user->id], [
            'expertise' => 'General', 'bio' => '', 'reputation' => 0,
        ]);

        return view('contributor.dashboard', compact(
            'myArticles', 'totalArticles', 'publishedArticles', 'draftArticles', 'totalViews', 'profile'
        ));
    }

    public function articles(Request $request)
    {
        $user     = auth()->user();
        $articles = Article::where('author_id', $user->id)
            ->with('category')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('contributor.articles', compact('articles'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('contributor.edit', ['article' => new Article(), 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'summary'     => 'required|string',
            'content'     => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'status'      => 'required|in:draft,review',
            'featured_image' => 'nullable|url|max:500',
            'read_time'   => 'nullable|integer|min:1',
        ]);
        $data['author_id'] = auth()->id();
        $data['slug']      = Str::slug($data['title']) . '-' . Str::random(5);
        Article::create($data);
        return redirect()->route('contributor.articles')->with('success', 'Article submitted for review.');
    }

    public function edit(Article $article)
    {
        $this->authorizeArticle($article);
        $categories = Category::orderBy('name')->get();
        return view('contributor.edit', compact('article', 'categories'));
    }

    public function update(Request $request, Article $article)
    {
        $this->authorizeArticle($article);
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'summary'     => 'required|string',
            'content'     => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'status'      => 'required|in:draft,review',
            'featured_image' => 'nullable|url|max:500',
            'read_time'   => 'nullable|integer|min:1',
        ]);
        $article->update($data);
        return redirect()->route('contributor.articles')->with('success', 'Article updated.');
    }

    public function destroy(Article $article)
    {
        $this->authorizeArticle($article);
        $article->delete();
        return redirect()->route('contributor.articles')->with('success', 'Article deleted.');
    }

    private function authorizeArticle(Article $article): void
    {
        if ($article->author_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403);
        }
    }
}
