@extends('layouts.app')

@section('title', 'My Articles | Curevia')

@section('content')
<section style="padding:7rem 0 3rem;position:relative;z-index:1;">
<div style="max-width:1280px;margin:0 auto;padding:0 1.5rem;">

    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:2rem;">
        <div>
            <h1 style="font-size:1.5rem;font-weight:800;color:var(--text-primary);">My Articles</h1>
            <p style="font-size:0.85rem;color:var(--text-muted);">{{ $articles->total() }} total</p>
        </div>
        <div style="display:flex;gap:0.75rem;">
            <a href="{{ route('contributor.dashboard') }}" style="padding:0.65rem 1.25rem;font-size:0.875rem;color:var(--text-muted);text-decoration:none;border:1px solid var(--border-subtle);border-radius:0.5rem;">← Dashboard</a>
            <a href="{{ route('contributor.articles.create') }}" class="btn-primary" style="display:inline-flex;align-items:center;gap:0.4rem;text-decoration:none;padding:0.65rem 1.25rem;font-size:0.875rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Article
            </a>
        </div>
    </div>

    @if(session('success'))
    <div style="background:rgba(34,242,226,0.1);border:1px solid var(--accent-cyan);color:var(--accent-cyan);padding:0.75rem 1rem;border-radius:0.75rem;margin-bottom:1.5rem;font-size:0.875rem;">{{ session('success') }}</div>
    @endif

    {{-- Status filter --}}
    <form method="GET" style="display:flex;gap:0.75rem;margin-bottom:1.5rem;">
        <select name="status" onchange="this.form.submit()" style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.5rem 0.875rem;color:var(--text-primary);font-size:0.875rem;outline:none;">
            <option value="">All Statuses</option>
            <option value="published" {{ request('status')==='published'?'selected':'' }}>Published</option>
            <option value="draft"     {{ request('status')==='draft'?'selected':'' }}>Draft</option>
            <option value="review"    {{ request('status')==='review'?'selected':'' }}>In Review</option>
        </select>
    </form>

    <div class="glass-card" style="padding:0;overflow:hidden;">
        <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;min-width:560px;">
            <thead>
                <tr style="border-bottom:1px solid var(--border-subtle);background:rgba(0,0,0,0.2);">
                    <th style="text-align:left;padding:1rem 1.25rem;font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;font-weight:600;">Title</th>
                    <th style="text-align:left;padding:1rem;font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;font-weight:600;">Category</th>
                    <th style="text-align:left;padding:1rem;font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;font-weight:600;">Views</th>
                    <th style="text-align:left;padding:1rem;font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;font-weight:600;">Status</th>
                    <th style="text-align:right;padding:1rem 1.25rem;font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;font-weight:600;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($articles as $article)
                <tr style="border-bottom:1px solid var(--border-subtle);">
                    <td style="padding:0.875rem 1.25rem;">
                        <div style="font-size:0.875rem;font-weight:600;color:var(--text-primary);">{{ Str::limit($article->title, 50) }}</div>
                        <div style="font-size:0.7rem;color:var(--text-muted);">{{ $article->created_at->format('M d, Y') }}</div>
                    </td>
                    <td style="padding:0.875rem 1rem;"><span class="trending-badge">{{ $article->category->name ?? '—' }}</span></td>
                    <td style="padding:0.875rem 1rem;font-size:0.85rem;color:var(--text-secondary);">{{ number_format($article->views) }}</td>
                    <td style="padding:0.875rem 1rem;">
                        @php $sc = ['published'=>'background:rgba(34,242,226,0.1);color:var(--accent-cyan);','draft'=>'background:rgba(245,158,11,0.1);color:var(--accent-gold);','review'=>'background:rgba(124,108,255,0.1);color:var(--accent-violet);'][$article->status] ?? ''; @endphp
                        <span style="font-size:0.7rem;font-weight:600;padding:0.2rem 0.6rem;border-radius:100px;{{ $sc }}">{{ ucfirst($article->status) }}</span>
                    </td>
                    <td style="padding:0.875rem 1.25rem;text-align:right;">
                        <div style="display:flex;gap:0.5rem;justify-content:flex-end;">
                            <a href="{{ route('contributor.articles.edit', $article) }}" style="padding:0.35rem 0.75rem;font-size:0.75rem;border-radius:0.4rem;background:rgba(34,242,226,0.1);color:var(--accent-cyan);text-decoration:none;font-weight:600;">Edit</a>
                            <form method="POST" action="{{ route('contributor.articles.destroy', $article) }}" onsubmit="return confirm('Delete this article?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="padding:0.35rem 0.75rem;font-size:0.75rem;border-radius:0.4rem;background:rgba(239,68,68,0.1);color:#f87171;border:none;cursor:pointer;font-weight:600;">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="padding:3rem;text-align:center;color:var(--text-muted);font-size:0.875rem;">No articles found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($articles->hasPages())
        <div style="padding:1rem 1.25rem;border-top:1px solid var(--border-subtle);display:flex;justify-content:flex-end;gap:0.5rem;">
            @if(!$articles->onFirstPage())
            <a href="{{ $articles->previousPageUrl() }}" style="padding:0.35rem 0.75rem;font-size:0.8rem;border-radius:0.4rem;border:1px solid var(--border-subtle);color:var(--text-primary);text-decoration:none;">← Prev</a>
            @endif
            @if($articles->hasMorePages())
            <a href="{{ $articles->nextPageUrl() }}" style="padding:0.35rem 0.75rem;font-size:0.8rem;border-radius:0.4rem;border:1px solid var(--border-subtle);color:var(--text-primary);text-decoration:none;">Next →</a>
            @endif
        </div>
        @endif
    </div>

</div>
</section>
@endsection
