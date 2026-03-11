<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\ContributorProfile;
use App\Models\Product;
use App\Models\Story;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalArticles = Article::count();
        $totalUsers = User::count();
        $totalPageViews = Article::sum('views') + Story::sum('views');
        $totalProducts = Product::count();

        $articlesThisWeek = Article::where('created_at', '>=', now()->subWeek())->count();
        $usersThisWeek = User::where('created_at', '>=', now()->subWeek())->count();

        $recentArticles = Article::with('category')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $topContributors = ContributorProfile::with('user')
            ->orderByDesc('reputation')
            ->take(4)
            ->get();

        return view('admin.dashboard', compact(
            'totalArticles', 'totalUsers', 'totalPageViews', 'totalProducts',
            'articlesThisWeek', 'usersThisWeek',
            'recentArticles', 'topContributors'
        ));
    }

    public function productsIndex()
    {
        $products = Product::orderByDesc('created_at')->get();
        return view('admin.products.index', compact('products'));
    }

    public function productsCreate()
    {
        return view('admin.products.edit', ['product' => new Product()]);
    }

    public function productsStore(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'slug'          => 'nullable|string|max:255|unique:products,slug',
            'description'   => 'nullable|string',
            'price'         => 'required|numeric|min:0',
            'original_price'=> 'nullable|numeric|min:0',
            'category'      => 'nullable|string|max:100',
            'badge'         => 'nullable|string|max:50',
            'image'         => 'nullable|url',
            'affiliate_url' => 'nullable|url',
            'is_active'     => 'boolean',
        ]);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);
        Product::create($data);
        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function productsEdit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function productsUpdate(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'price'         => 'required|numeric|min:0',
            'original_price'=> 'nullable|numeric|min:0',
            'category'      => 'nullable|string|max:100',
            'badge'         => 'nullable|string|max:50',
            'image'         => 'nullable|url',
            'affiliate_url' => 'nullable|url',
            'is_active'     => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $product->update($data);
        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function productsDestroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    /* ─── ARTICLES ─── */
    public function articlesIndex(Request $request)
    {
        $articles = Article::with(['category', 'author'])
            ->when($request->q, fn($q, $s) => $q->where('title', 'like', "%$s%"))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->category, fn($q, $c) => $q->where('category_id', $c))
            ->orderByDesc('created_at')
            ->paginate(20);
        $categories = Category::orderBy('name')->get();
        return view('admin.articles.index', compact('articles', 'categories'));
    }

    public function articlesCreate()
    {
        $categories = Category::orderBy('name')->get();
        $contributors = User::whereIn('role', ['admin', 'contributor'])->orderBy('name')->get();
        return view('admin.articles.edit', ['article' => new Article(), 'categories' => $categories, 'contributors' => $contributors]);
    }

    public function articlesStore(Request $request)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'slug'          => 'nullable|string|max:255|unique:articles,slug',
            'summary'       => 'required|string',
            'content'       => 'required|string',
            'category_id'   => 'required|exists:categories,id',
            'author_id'     => 'required|exists:users,id',
            'status'        => 'required|in:draft,review,published',
            'featured_image'=> 'nullable|url|max:500',
            'read_time'     => 'nullable|integer|min:1',
        ]);
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        if ($data['status'] === 'published') $data['published_at'] = now();
        Article::create($data);
        return redirect()->route('admin.articles.index')->with('success', 'Article created.');
    }

    public function articlesEdit(Article $article)
    {
        $categories = Category::orderBy('name')->get();
        $contributors = User::whereIn('role', ['admin', 'contributor'])->orderBy('name')->get();
        return view('admin.articles.edit', compact('article', 'categories', 'contributors'));
    }

    public function articlesUpdate(Request $request, Article $article)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'summary'       => 'required|string',
            'content'       => 'required|string',
            'category_id'   => 'required|exists:categories,id',
            'author_id'     => 'required|exists:users,id',
            'status'        => 'required|in:draft,review,published',
            'featured_image'=> 'nullable|url|max:500',
            'read_time'     => 'nullable|integer|min:1',
        ]);
        if ($data['status'] === 'published' && !$article->published_at) {
            $data['published_at'] = now();
        }
        $article->update($data);
        return redirect()->route('admin.articles.index')->with('success', 'Article updated.');
    }

    public function articlesDestroy(Article $article)
    {
        $article->delete();
        return redirect()->route('admin.articles.index')->with('success', 'Article deleted.');
    }

    /* ─── USERS ─── */
    public function usersIndex(Request $request)
    {
        $users = User::withCount(['contributorProfile as articles_count' => function ($q) {
                // join articles authored
            }])
            ->when($request->q, fn($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"))
            ->when($request->role, fn($q, $r) => $q->where('role', $r))
            ->orderByDesc('created_at')
            ->paginate(25);
        return view('admin.users.index', compact('users'));
    }

    public function usersEdit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function usersUpdate(Request $request, User $user)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role'  => 'required|in:admin,contributor,user',
        ]);
        $user->update($data);
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $user->update(['password' => bcrypt($request->password)]);
        }
        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function usersDestroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    /* ─── ANALYTICS ─── */
    public function analytics()
    {
        $totalViews = Article::sum('views') + Story::sum('views');
        $topArticles = Article::with('category')->orderByDesc('views')->take(10)->get();
        $topStories = Story::with('category')->orderByDesc('views')->take(5)->get();
        $usersByRole = User::selectRaw('role, count(*) as count')->groupBy('role')->get();
        $articlesByCategory = Category::withCount('articles')->orderByDesc('articles_count')->get();
        $recentUsers = User::orderByDesc('created_at')->take(8)->get();
        return view('admin.analytics', compact(
            'totalViews', 'topArticles', 'topStories', 'usersByRole', 'articlesByCategory', 'recentUsers'
        ));
    }
}
