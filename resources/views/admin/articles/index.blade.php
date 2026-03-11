<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Articles | Curevia Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background:#0B0F14;color:#F0F4FF;font-family:'Inter',system-ui,sans-serif;">

@include('admin.layouts.sidebar')

<main style="margin-left:260px;padding:2rem;" id="admin-main">

    <button id="admin-sidebar-toggle" onclick="document.getElementById('admin-sidebar').classList.toggle('open')" style="display:none;position:fixed;top:1rem;left:1rem;z-index:1001;width:40px;height:40px;border-radius:10px;background:rgba(17,24,39,0.9);border:1px solid var(--border-glow);color:var(--accent-cyan);cursor:pointer;align-items:center;justify-content:center;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;">
        <div>
            <h1 style="font-size:1.5rem;font-weight:800;">Articles</h1>
            <p style="font-size:0.85rem;color:var(--text-muted);">{{ $articles->total() }} total articles</p>
        </div>
        <a href="{{ route('admin.articles.create') }}" class="btn-primary" style="display:inline-flex;align-items:center;gap:0.5rem;text-decoration:none;font-size:0.85rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Article
        </a>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
    <div style="background:rgba(34,242,226,0.1);border:1px solid var(--accent-cyan);color:var(--accent-cyan);padding:0.75rem 1rem;border-radius:0.75rem;margin-bottom:1.5rem;font-size:0.875rem;">{{ session('success') }}</div>
    @endif

    {{-- Filters --}}
    <form method="GET" style="display:flex;gap:0.75rem;margin-bottom:1.5rem;flex-wrap:wrap;">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search articles…" style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.5rem 0.875rem;color:var(--text-primary);font-size:0.875rem;min-width:200px;outline:none;">
        <select name="status" style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.5rem 0.875rem;color:var(--text-primary);font-size:0.875rem;outline:none;">
            <option value="">All Statuses</option>
            <option value="published" {{ request('status')==='published'?'selected':'' }}>Published</option>
            <option value="draft" {{ request('status')==='draft'?'selected':'' }}>Draft</option>
            <option value="review" {{ request('status')==='review'?'selected':'' }}>In Review</option>
        </select>
        <select name="category" style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.5rem 0.875rem;color:var(--text-primary);font-size:0.875rem;outline:none;">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary" style="padding:0.5rem 1rem;font-size:0.875rem;">Filter</button>
        @if(request()->anyFilled(['q','status','category']))
        <a href="{{ route('admin.articles.index') }}" style="padding:0.5rem 1rem;font-size:0.875rem;color:var(--text-muted);text-decoration:none;border:1px solid var(--border-subtle);border-radius:0.5rem;">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;overflow:hidden;">
        <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;min-width:700px;">
            <thead>
                <tr style="border-bottom:1px solid var(--border-subtle);background:rgba(0,0,0,0.2);">
                    <th style="text-align:left;padding:1rem 1.25rem;font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">Title</th>
                    <th style="text-align:left;padding:1rem 1rem;font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">Category</th>
                    <th style="text-align:left;padding:1rem 1rem;font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">Author</th>
                    <th style="text-align:left;padding:1rem 1rem;font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">Views</th>
                    <th style="text-align:left;padding:1rem 1rem;font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">Status</th>
                    <th style="text-align:left;padding:1rem 1rem;font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">Date</th>
                    <th style="text-align:right;padding:1rem 1.25rem;font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($articles as $article)
                <tr style="border-bottom:1px solid var(--border-subtle);transition:background 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='transparent'">
                    <td style="padding:0.875rem 1.25rem;">
                        <div style="font-size:0.875rem;font-weight:600;color:var(--text-primary);max-width:280px;">{{ Str::limit($article->title, 50) }}</div>
                        @if($article->slug)
                        <div style="font-size:0.7rem;color:var(--text-muted);margin-top:0.1rem;">/encyclopedia/{{ $article->slug }}</div>
                        @endif
                    </td>
                    <td style="padding:0.875rem 1rem;"><span class="trending-badge">{{ $article->category->name ?? '—' }}</span></td>
                    <td style="padding:0.875rem 1rem;font-size:0.8rem;color:var(--text-secondary);">{{ $article->author->name ?? '—' }}</td>
                    <td style="padding:0.875rem 1rem;font-size:0.85rem;color:var(--text-secondary);">{{ number_format($article->views) }}</td>
                    <td style="padding:0.875rem 1rem;">
                        @php
                        $sc = ['published'=>'background:rgba(34,242,226,0.1);color:var(--accent-cyan);','draft'=>'background:rgba(245,158,11,0.1);color:var(--accent-gold);','review'=>'background:rgba(124,108,255,0.1);color:var(--accent-violet);'][$article->status] ?? '';
                        @endphp
                        <span style="font-size:0.7rem;font-weight:600;padding:0.2rem 0.6rem;border-radius:100px;{{ $sc }}">{{ ucfirst($article->status) }}</span>
                    </td>
                    <td style="padding:0.875rem 1rem;font-size:0.8rem;color:var(--text-muted);">{{ $article->created_at->format('M d, Y') }}</td>
                    <td style="padding:0.875rem 1.25rem;text-align:right;">
                        <div style="display:flex;gap:0.5rem;justify-content:flex-end;">
                            <a href="{{ route('admin.articles.edit', $article) }}" style="padding:0.35rem 0.75rem;font-size:0.75rem;border-radius:0.4rem;background:rgba(34,242,226,0.1);color:var(--accent-cyan);text-decoration:none;font-weight:600;">Edit</a>
                            <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" onsubmit="return confirm('Delete this article?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="padding:0.35rem 0.75rem;font-size:0.75rem;border-radius:0.4rem;background:rgba(239,68,68,0.1);color:#f87171;border:none;cursor:pointer;font-weight:600;">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding:3rem;text-align:center;color:var(--text-muted);font-size:0.875rem;">No articles found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        {{-- Pagination --}}
        @if($articles->hasPages())
        <div style="padding:1rem 1.25rem;border-top:1px solid var(--border-subtle);display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:0.8rem;color:var(--text-muted);">Showing {{ $articles->firstItem() }}–{{ $articles->lastItem() }} of {{ $articles->total() }}</span>
            <div style="display:flex;gap:0.5rem;">
                @if($articles->onFirstPage())
                <span style="padding:0.35rem 0.75rem;font-size:0.8rem;border-radius:0.4rem;border:1px solid var(--border-subtle);color:var(--text-muted);">← Prev</span>
                @else
                <a href="{{ $articles->previousPageUrl() }}" style="padding:0.35rem 0.75rem;font-size:0.8rem;border-radius:0.4rem;border:1px solid var(--border-subtle);color:var(--text-primary);text-decoration:none;">← Prev</a>
                @endif
                @if($articles->hasMorePages())
                <a href="{{ $articles->nextPageUrl() }}" style="padding:0.35rem 0.75rem;font-size:0.8rem;border-radius:0.4rem;border:1px solid var(--border-subtle);color:var(--text-primary);text-decoration:none;">Next →</a>
                @else
                <span style="padding:0.35rem 0.75rem;font-size:0.8rem;border-radius:0.4rem;border:1px solid var(--border-subtle);color:var(--text-muted);">Next →</span>
                @endif
            </div>
        </div>
        @endif
    </div>

</main>

<style>
@media (max-width: 1024px) {
    #admin-sidebar { transform: translateX(-100%); position: fixed; top:0; left:0; bottom:0; z-index:1000; transition: transform 0.3s ease; }
    #admin-sidebar.open { transform: translateX(0); }
    #admin-main { margin-left: 0 !important; }
    #admin-sidebar-toggle { display: flex !important; }
}
@media (max-width: 640px) {
    #admin-main { padding: 1rem !important; padding-top: 3.5rem !important; }
}
</style>

</body>
</html>
