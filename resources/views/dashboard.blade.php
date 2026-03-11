@extends('layouts.app')

@section('title', 'Dashboard | Curevia')

@section('content')
<section style="padding:7rem 0 3rem;position:relative;z-index:1;">
    <div style="max-width:1280px;margin:0 auto;padding:0 1.5rem;">

        {{-- Welcome --}}
        <div style="margin-bottom:3rem;">
            <h1 style="font-size:1.75rem;font-weight:800;color:var(--text-primary);margin-bottom:0.5rem;">Welcome back{{ auth()->check() ? ', ' . auth()->user()->name : '' }}!</h1>
            <p style="color:var(--text-secondary);">Your personal knowledge dashboard.</p>
        </div>

        {{-- Stats Cards --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.25rem;margin-bottom:3rem;">
            @foreach([
                ['label' => 'Bookmarks', 'value' => '24', 'color' => 'var(--accent-cyan)', 'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>'],
                ['label' => 'Articles Read', 'value' => '156', 'color' => 'var(--accent-violet)', 'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>'],
                ['label' => 'Comments', 'value' => '12', 'color' => 'var(--accent-emerald)', 'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>'],
                ['label' => 'Orders', 'value' => '3', 'color' => 'var(--accent-gold)', 'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>'],
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

        {{-- Recent Bookmarks --}}
        <div style="margin-bottom:3rem;">
            <h2 style="font-size:1.25rem;font-weight:700;color:var(--text-primary);margin-bottom:1.25rem;">Recent Bookmarks</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.25rem;">
                @foreach([
                    ['title' => 'Black Holes', 'slug' => 'black-holes', 'category' => 'Space', 'img' => 'https://images.unsplash.com/photo-1462331940025-496dfbfc7564?w=400&q=80'],
                    ['title' => 'Human Brain', 'slug' => 'human-brain', 'category' => 'Human Body', 'img' => 'https://images.unsplash.com/photo-1559757175-5700dde675bc?w=400&q=80'],
                    ['title' => 'Roman Empire', 'slug' => 'roman-empire', 'category' => 'Civilizations', 'img' => 'https://images.unsplash.com/photo-1552832230-c0197dd311b5?w=400&q=80'],
                ] as $bm)
                <a href="{{ route('encyclopedia.show', $bm['slug']) }}" class="glass-card" style="text-decoration:none;display:flex;align-items:center;gap:1rem;padding:1rem;">
                    <img src="{{ $bm['img'] }}" alt="{{ $bm['title'] }}" style="width:64px;height:64px;border-radius:0.75rem;object-fit:cover;" loading="lazy" width="400" height="400">
                    <div>
                        <span class="trending-badge" style="margin-bottom:0.3rem;">{{ $bm['category'] }}</span>
                        <h4 style="font-size:0.95rem;font-weight:700;color:var(--text-primary);">{{ $bm['title'] }}</h4>
                    </div>
                </a>
                @endforeach
            </div>
        </div>

        {{-- Quick Actions --}}
        <div>
            <h2 style="font-size:1.25rem;font-weight:700;color:var(--text-primary);margin-bottom:1.25rem;">Quick Actions</h2>
            <div style="display:flex;flex-wrap:wrap;gap:1rem;">
                <a href="{{ route('encyclopedia.index') }}" class="btn-primary" style="padding:0.75rem 1.5rem;font-size:0.85rem;">Browse Encyclopedia</a>
                <a href="{{ route('stories.index') }}" class="btn-secondary" style="padding:0.75rem 1.5rem;font-size:0.85rem;">Read Stories</a>
                <a href="{{ route('shop.index') }}" class="btn-violet" style="padding:0.75rem 1.5rem;font-size:0.85rem;">Shop Products</a>
            </div>
        </div>

    </div>
</section>
@endsection
