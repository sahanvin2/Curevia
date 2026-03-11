@extends('layouts.app')

@section('title', 'Dashboard | Curevia')

@section('content')
<section style="padding:7rem 0 3rem;position:relative;z-index:1;">
    <div style="max-width:1280px;margin:0 auto;padding:0 1.5rem;">

        {{-- Welcome --}}
        <div style="margin-bottom:3rem;">
            <h1 style="font-size:1.75rem;font-weight:800;color:var(--text-primary);margin-bottom:0.5rem;">Welcome back, {{ auth()->user()->name }}!</h1>
            <p style="color:var(--text-secondary);">Your personal knowledge dashboard.</p>
        </div>

        {{-- Stats Cards --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.25rem;margin-bottom:3rem;">
            @foreach([
                ['label' => 'Bookmarks',     'value' => $bookmarkCount,  'color' => 'var(--accent-cyan)',    'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>'],
                ['label' => 'Comments Made', 'value' => $commentCount,   'color' => 'var(--accent-emerald)', 'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>'],
            ] as $stat)
            <div class="stat-card" style="display:flex;align-items:center;gap:1rem;">
                <div style="width:48px;height:48px;border-radius:1rem;background:rgba(34,242,226,0.06);display:flex;align-items:center;justify-content:center;color:{{ $stat['color'] }};">
                    {!! $stat['icon'] !!}
                </div>
                <div>
                    <div style="font-size:1.5rem;font-weight:800;color:var(--text-primary);">{{ $stat['value'] }}</div>
                    <div style="font-size:0.8rem;color:var(--text-muted);">{{ $stat['label'] }}</div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Bookmarks --}}
        <div style="margin-bottom:3rem;">
            <h2 style="font-size:1.25rem;font-weight:700;color:var(--text-primary);margin-bottom:1.25rem;">Your Bookmarks</h2>
            @if($bookmarks->isEmpty())
            <div class="glass-card" style="padding:2.5rem;text-align:center;">
                <p style="color:var(--text-muted);margin-bottom:1rem;">You haven't bookmarked anything yet.</p>
                <a href="{{ route('encyclopedia.index') }}" class="btn-primary" style="display:inline-block;padding:0.65rem 1.5rem;font-size:0.85rem;">Explore Encyclopedia</a>
            </div>
            @else
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.25rem;">
                @foreach($bookmarks as $bm)
                @php $article = $bm->bookmarkable; @endphp
                <a href="{{ route('encyclopedia.show', $article->slug) }}" class="glass-card" style="text-decoration:none;display:flex;align-items:center;gap:1rem;padding:1rem;">
                    @if($article->featured_image)
                    <img src="{{ $article->featured_image }}" alt="{{ $article->title }}" style="width:64px;height:64px;border-radius:0.75rem;object-fit:cover;flex-shrink:0;" loading="lazy" width="64" height="64">
                    @else
                    <div style="width:64px;height:64px;border-radius:0.75rem;background:linear-gradient(135deg,var(--accent-cyan),var(--accent-violet));display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="1.5"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    </div>
                    @endif
                    <div style="overflow:hidden;">
                        @if($article->category)
                        <span class="trending-badge" style="margin-bottom:0.3rem;display:inline-block;">{{ $article->category->name }}</span>
                        @endif
                        <h4 style="font-size:0.9rem;font-weight:700;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $article->title }}</h4>
                    </div>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Quick Actions --}}
        <div>
            <h2 style="font-size:1.25rem;font-weight:700;color:var(--text-primary);margin-bottom:1.25rem;">Quick Actions</h2>
            <div style="display:flex;flex-wrap:wrap;gap:1rem;">
                <a href="{{ route('encyclopedia.index') }}" class="btn-primary" style="padding:0.75rem 1.5rem;font-size:0.85rem;">Browse Encyclopedia</a>
                <a href="{{ route('stories.index') }}" class="btn-secondary" style="padding:0.75rem 1.5rem;font-size:0.85rem;">Read Stories</a>
                <a href="{{ route('shop.index') }}" class="btn-violet" style="padding:0.75rem 1.5rem;font-size:0.85rem;">Shop Products</a>
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="btn-primary" style="padding:0.75rem 1.5rem;font-size:0.85rem;background:linear-gradient(135deg,#f59e0b,#ef4444);">Admin Panel</a>
                @endif
                @if(auth()->user()->role === 'contributor')
                <a href="{{ route('contributor.dashboard') }}" class="btn-primary" style="padding:0.75rem 1.5rem;font-size:0.85rem;background:linear-gradient(135deg,var(--accent-violet),var(--accent-cyan));">Contributor Panel</a>
                @endif
            </div>
        </div>

    </div>
</section>
@endsection
