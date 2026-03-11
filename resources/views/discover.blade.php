@extends('layouts.app')

@section('title', 'Discover — Explore Knowledge | Curevia')
@section('meta_description', 'Discover fascinating topics, curated collections, and knowledge pathways on Curevia.')

@section('content')

{{-- Header --}}
<section style="padding:8rem 0 3rem;position:relative;z-index:1;">
    <div style="max-width:1280px;margin:0 auto;padding:0 1.5rem;">
        <div class="section-header" style="margin-bottom:2rem;">
            <div class="section-label">Discover</div>
            <h1 class="section-title">Explore the Unknown</h1>
            <p class="section-desc">Curated pathways through the vast ocean of knowledge. Let curiosity guide you.</p>
        </div>
    </div>
</section>

{{-- Knowledge Domains (from DB categories) --}}
<section style="max-width:1280px;margin:0 auto;padding:0 1.5rem 4rem;">
    <h2 style="font-size:1.25rem;font-weight:700;color:var(--text-primary);margin-bottom:1.5rem;">Knowledge Domains</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.5rem;">
        @php
        $catIcons = [
            'space' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#22F2E2" stroke-width="1.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>',
            'earth' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="1.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
            'science' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#7C6CFF" stroke-width="1.5" stroke-linecap="round"><path d="M9 3h6M10 3v7.5L6 21h12l-4-10.5V3"/><circle cx="12" cy="17" r="1.5" fill="#7C6CFF"/></svg>',
            'history' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="1.5" stroke-linecap="round"><path d="M3 21h18M5 21V7l7-4 7 4v14"/><rect x="9" y="13" width="6" height="8"/><rect x="10" y="8" width="4" height="3"/></svg>',
            'animals' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#EC4899" stroke-width="1.5" stroke-linecap="round"><path d="M12 19c-4 0-7-2-7-5 0-2 1-3 2-4l1-5c0-1 1-2 2-2s2 1 2 2l0 3h0l0-3c0-1 1-2 2-2s2 1 2 2l1 5c1 1 2 2 2 4 0 3-3 5-7 5z"/></svg>',
            'human-body' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#2DD4BF" stroke-width="1.5" stroke-linecap="round"><circle cx="12" cy="5" r="3"/><path d="M12 8v8M8 12h8M12 16l-3 5M12 16l3 5"/></svg>',
            'countries' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#22F2E2" stroke-width="1.5" stroke-linecap="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>',
            'nature' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="1.5" stroke-linecap="round"><path d="M7 20h10M12 20V10"/><path d="M17 10c0-4-2.5-8-5-8S7 6 7 10"/></svg>',
            'mythology' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#7C6CFF" stroke-width="1.5" stroke-linecap="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
            'zodiac' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="1.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/><line x1="12" y1="2" x2="12" y2="8"/><line x1="12" y1="16" x2="12" y2="22"/><line x1="2" y1="12" x2="8" y2="12"/><line x1="16" y1="12" x2="22" y2="12"/></svg>',
            'civilizations' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#EC4899" stroke-width="1.5" stroke-linecap="round"><rect x="3" y="11" width="18" height="10" rx="1"/><path d="M12 3l8 8H4l8-8z"/><line x1="8" y1="15" x2="8" y2="21"/><line x1="12" y1="15" x2="12" y2="21"/><line x1="16" y1="15" x2="16" y2="21"/></svg>',
            'technology' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#2DD4BF" stroke-width="1.5" stroke-linecap="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>',
        ];
        $catGradients = [
            'space' => 'linear-gradient(135deg, rgba(34,242,226,0.12), rgba(124,108,255,0.12))',
            'earth' => 'linear-gradient(135deg, rgba(52,211,153,0.12), rgba(45,212,191,0.12))',
            'science' => 'linear-gradient(135deg, rgba(124,108,255,0.12), rgba(34,242,226,0.12))',
            'history' => 'linear-gradient(135deg, rgba(245,158,11,0.12), rgba(236,72,153,0.12))',
            'animals' => 'linear-gradient(135deg, rgba(236,72,153,0.12), rgba(245,158,11,0.12))',
            'human-body' => 'linear-gradient(135deg, rgba(45,212,191,0.12), rgba(52,211,153,0.12))',
            'countries' => 'linear-gradient(135deg, rgba(34,242,226,0.12), rgba(52,211,153,0.12))',
            'nature' => 'linear-gradient(135deg, rgba(52,211,153,0.12), rgba(34,242,226,0.12))',
            'mythology' => 'linear-gradient(135deg, rgba(124,108,255,0.12), rgba(236,72,153,0.12))',
            'zodiac' => 'linear-gradient(135deg, rgba(245,158,11,0.12), rgba(124,108,255,0.12))',
            'civilizations' => 'linear-gradient(135deg, rgba(236,72,153,0.12), rgba(124,108,255,0.12))',
            'technology' => 'linear-gradient(135deg, rgba(45,212,191,0.12), rgba(34,242,226,0.12))',
        ];
        @endphp

        @foreach($categories as $cat)
        <a href="{{ route('encyclopedia.index', ['category' => $cat->slug]) }}" class="glass-card reveal" style="text-decoration:none;padding:1.75rem;background:{{ $catGradients[$cat->slug] ?? 'linear-gradient(135deg, rgba(34,242,226,0.08), rgba(124,108,255,0.08))' }};">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1rem;">
                <div style="width:48px;height:48px;border-radius:0.75rem;background:rgba(17,24,39,0.6);border:1px solid rgba(34,242,226,0.1);display:flex;align-items:center;justify-content:center;">
                    {!! $catIcons[$cat->slug] ?? '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#22F2E2" stroke-width="1.5"><circle cx="12" cy="12" r="10"/></svg>' !!}
                </div>
                <span style="font-size:0.7rem;color:var(--accent-cyan);font-weight:600;background:rgba(34,242,226,0.08);padding:0.3rem 0.75rem;border-radius:2rem;">{{ $cat->articles_count }} articles</span>
            </div>
            <h3 style="font-size:1.1rem;font-weight:700;color:var(--text-primary);margin-bottom:0.4rem;">{{ $cat->name }}</h3>
            <p style="font-size:0.825rem;color:var(--text-secondary);line-height:1.6;">{{ $cat->description ?: 'Explore ' . $cat->name . ' topics and articles.' }}</p>
        </a>
        @endforeach
    </div>
</section>

{{-- Random Discovery --}}
<section style="max-width:1280px;margin:0 auto;padding:0 1.5rem 4rem;">
    <div style="background:linear-gradient(135deg,rgba(34,242,226,0.06),rgba(124,108,255,0.06));border:1px solid var(--border-glow);border-radius:1.5rem;padding:3rem;text-align:center;">
        <h2 style="font-size:1.5rem;font-weight:800;color:var(--text-primary);margin-bottom:0.75rem;">Feeling Curious?</h2>
        <p style="color:var(--text-secondary);margin-bottom:1.5rem;">Let us take you somewhere unexpected in the ocean of knowledge.</p>
        @if($randomArticles->count() > 0)
        <a href="{{ route('encyclopedia.show', $randomArticles->first()->slug) }}" class="btn-primary" style="padding:0.9rem 2.5rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
            Random Discovery
        </a>
        @endif
    </div>
</section>

{{-- Trending Articles --}}
@if($topArticles->count() > 0)
<section style="max-width:1280px;margin:0 auto;padding:0 1.5rem 4rem;">
    <h2 style="font-size:1.25rem;font-weight:700;color:var(--text-primary);margin-bottom:1.5rem;">Trending Articles</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(350px,1fr));gap:1.5rem;">
        @foreach($topArticles as $a)
        <a href="{{ route('encyclopedia.show', $a->slug) }}" class="glass-card reveal" style="text-decoration:none;padding:0;overflow:hidden;">
            <div style="position:relative;height:180px;overflow:hidden;">
                <img src="{{ $a->featured_image }}" alt="{{ $a->title }}" style="width:100%;height:100%;object-fit:cover;" loading="lazy">
                <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(11,15,20,0.9) 0%,transparent 60%);"></div>
                <div style="position:absolute;bottom:1rem;left:1rem;right:1rem;">
                    <span style="font-size:0.65rem;color:var(--accent-cyan);font-weight:600;text-transform:uppercase;letter-spacing:0.08em;">{{ $a->category->name ?? 'General' }}</span>
                    <h3 style="font-size:1rem;font-weight:700;color:var(--text-primary);margin-top:0.25rem;line-height:1.4;">{{ $a->title }}</h3>
                </div>
            </div>
            <div style="padding:1rem 1.25rem;display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:0.75rem;color:var(--text-muted);">{{ $a->read_time }} min read</span>
                <span style="font-size:0.75rem;color:var(--text-muted);">{{ number_format($a->views) }} views</span>
            </div>
        </a>
        @endforeach
    </div>
</section>
@endif

{{-- Knowledge Stats --}}
<section style="max-width:1280px;margin:0 auto;padding:0 1.5rem 4rem;">
    <h2 style="font-size:1.25rem;font-weight:700;color:var(--text-primary);margin-bottom:1.5rem;text-align:center;">Curevia by the Numbers</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1.5rem;">
        @php
        $stats = [
            ['label' => 'Encyclopedia Articles', 'value' => number_format($totalArticles), 'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--accent-cyan)" stroke-width="1.5" stroke-linecap="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><line x1="8" y1="7" x2="16" y2="7"/><line x1="8" y1="11" x2="14" y2="11"/></svg>'],
            ['label' => 'Knowledge Domains', 'value' => $categories->count(), 'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--accent-violet)" stroke-width="1.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>'],
            ['label' => 'Stories Published', 'value' => number_format($totalStories), 'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#2DD4BF" stroke-width="1.5" stroke-linecap="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>'],
            ['label' => 'Total Views', 'value' => number_format($totalViews), 'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="1.5" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>'],
        ];
        @endphp

        @foreach($stats as $stat)
        <div class="stat-card reveal" style="text-align:center;padding:2rem 1rem;">
            <div style="display:flex;justify-content:center;margin-bottom:0.75rem;">{!! $stat['icon'] !!}</div>
            <div style="font-size:1.75rem;font-weight:900;color:var(--text-primary);margin-bottom:0.25rem;">{{ $stat['value'] }}</div>
            <div style="font-size:0.8rem;color:var(--text-muted);">{{ $stat['label'] }}</div>
        </div>
        @endforeach
    </div>
</section>

{{-- Topic Cloud --}}
<section style="max-width:1280px;margin:0 auto;padding:0 1.5rem 5rem;">
    <h2 style="font-size:1.25rem;font-weight:700;color:var(--text-primary);margin-bottom:1.5rem;text-align:center;">Popular Topics</h2>
    <div style="display:flex;flex-wrap:wrap;gap:0.6rem;justify-content:center;">
        @foreach(['Black Holes', 'Mars', 'DNA', 'Pyramids', 'Quantum Physics', 'Blue Whale', 'Volcanoes', 'Norse Mythology', 'Human Brain', 'Amazon', 'Dark Matter', 'Dinosaurs', 'Antarctica', 'Solar System', 'Greek Gods', 'Machine Learning', 'Coral Reefs', 'Renaissance', 'Photosynthesis', 'Bermuda Triangle', 'Milky Way', 'Zodiac Signs', 'Evolution', 'Rainforests', 'Deep Sea'] as $topic)
        <a href="{{ route('encyclopedia.index', ['q' => $topic]) }}" class="search-tag" style="font-size:0.8rem;padding:0.4rem 1rem;">{{ $topic }}</a>
        @endforeach
    </div>
</section>

@endsection
