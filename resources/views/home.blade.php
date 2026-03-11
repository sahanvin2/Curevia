@extends('layouts.app')

@section('title', 'Curevia — The Ocean of Knowledge')
@section('meta_description', 'Explore the universe of knowledge. Science, space, history, geography, animals, mythology, civilizations and more.')

@section('schema_markup')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "WebSite",
    "name": "Curevia",
    "url": "{{ url('/') }}",
    "description": "A futuristic knowledge encyclopedia platform.",
    "potentialAction": {
        "@@type": "SearchAction",
        "target": "{{ url('/encyclopedia') }}?q={search_term_string}",
        "query-input": "required name=search_term_string"
    }
}
</script>
@endsection

@section('content')

{{-- ═══════════════════════════════════════════
     HERO SECTION — Animated Paper Boat
═══════════════════════════════════════════ --}}
<section class="hero-section" id="hero">
    {{-- Spline 3D Background --}}
    <div class="hero-spline">
        <iframe src='https://my.spline.design/animatedpaperboat-kxeygmsU1fVuNU8XzYimRzbv/' frameborder='0' width='100%' height='100%' style="pointer-events:none;" loading="lazy" title="Animated paper boat on ocean"></iframe>
    </div>

    {{-- Gradient Overlay --}}
    <div class="hero-overlay"></div>

    {{-- Content --}}
    <div class="hero-content">
        <div class="hero-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            The Future of Knowledge
        </div>

        <h1 class="hero-title">
            Navigate The<br>
            <span class="gradient-text">Ocean of Knowledge</span>
        </h1>

        <p class="hero-subtitle">
            Discover the universe — from distant galaxies to ancient civilizations,
            from the depths of the ocean to the wonders of the human body.
        </p>

        {{-- Search Bar --}}
        <div class="search-container">
            <form action="{{ route('encyclopedia.index') }}" method="GET">
                <input type="text" name="q" id="hero-search" class="search-bar" placeholder="Explore the Ocean of Knowledge..." autocomplete="off">
                <button type="submit" class="search-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>
                    Search
                </button>
            </form>
        </div>

        {{-- Search Tags --}}
        <div class="search-tags">
            @foreach(['Black Holes', 'Mars', 'Ancient Egypt', 'Human Brain', 'Zodiac', 'Amazon Rainforest', 'Milky Way'] as $tag)
            <a href="{{ route('encyclopedia.index', ['q' => $tag]) }}" class="search-tag">{{ $tag }}</a>
            @endforeach
        </div>
    </div>

    {{-- Scroll indicator --}}
    <div style="position:absolute;bottom:2rem;left:50%;transform:translateX(-50%);z-index:5;text-align:center;animation:float 3s ease-in-out infinite;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgba(34,242,226,0.5)" stroke-width="1.5" stroke-linecap="round"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
        <p style="font-size:0.65rem;color:var(--text-muted);margin-top:0.25rem;letter-spacing:0.1em;">SCROLL TO EXPLORE</p>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     KNOWLEDGE DISCOVERY GRID
═══════════════════════════════════════════ --}}
<section style="max-width:1280px;margin:0 auto;padding:5rem 1.5rem;" id="explore">
    <div class="section-header">
        <div class="section-label">Explore Domains</div>
        <h2 class="section-title">The Universe of Knowledge</h2>
        <p class="section-desc">Twelve gateways into the vast ocean of human understanding and discovery.</p>
    </div>

    <div class="domain-grid">
        @php
        $domains = [
            ['name' => 'Space', 'slug' => 'space', 'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#22F2E2" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20M2 12h20"/></svg>', 'color' => 'rgba(34,242,226,0.15)', 'glow' => 'rgba(34,242,226,0.2)', 'count' => '2,480'],
            ['name' => 'Earth', 'slug' => 'earth', 'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#2DD4BF" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2c2.5 3 4 7 4 10s-1.5 7-4 10c-2.5-3-4-7-4-10s1.5-7 4-10z"/></svg>', 'color' => 'rgba(45,212,191,0.15)', 'glow' => 'rgba(45,212,191,0.2)', 'count' => '3,120'],
            ['name' => 'Science', 'slug' => 'science', 'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#7C6CFF" stroke-width="1.5"><path d="M9 3v7.4L4.2 18.3A2 2 0 0 0 5.8 21h12.4a2 2 0 0 0 1.6-2.7L15 10.4V3"/><path d="M8 3h8"/><path d="M10 12h4"/></svg>', 'color' => 'rgba(124,108,255,0.15)', 'glow' => 'rgba(124,108,255,0.2)', 'count' => '4,890'],
            ['name' => 'History', 'slug' => 'history', 'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>', 'color' => 'rgba(245,158,11,0.15)', 'glow' => 'rgba(245,158,11,0.2)', 'count' => '5,340'],
            ['name' => 'Animals', 'slug' => 'animals', 'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#22F2E2" stroke-width="1.5"><path d="M12 21c-4.4 0-8-2.2-8-5 0-2 2-3.5 3-5 1.5-2.3 1-5 3-7 .4 2 2 3 4 3h2c2 0 3.6-1 4-3 2 2 1.5 4.7 3 7 1 1.5 3 3 3 5 0 2.8-3.6 5-8 5z"/><circle cx="9" cy="14" r="1" fill="#22F2E2"/><circle cx="15" cy="14" r="1" fill="#22F2E2"/></svg>', 'color' => 'rgba(34,242,226,0.15)', 'glow' => 'rgba(34,242,226,0.2)', 'count' => '6,750'],
            ['name' => 'Human Body', 'slug' => 'human-body', 'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#EC4899" stroke-width="1.5"><path d="M12 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6zM20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/></svg>', 'color' => 'rgba(236,72,153,0.15)', 'glow' => 'rgba(236,72,153,0.2)', 'count' => '2,180'],
            ['name' => 'Countries', 'slug' => 'countries', 'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#2DD4BF" stroke-width="1.5"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>', 'color' => 'rgba(45,212,191,0.15)', 'glow' => 'rgba(45,212,191,0.2)', 'count' => '1,950'],
            ['name' => 'Nature', 'slug' => 'nature', 'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="1.5"><path d="M7 20h10M12 20V10M17 10c0-4-2.5-8-5-8S7 6 7 10"/><path d="M14 14c2-1.5 5-1 5 2"/><path d="M10 14c-2-1.5-5-1-5 2"/></svg>', 'color' => 'rgba(52,211,153,0.15)', 'glow' => 'rgba(52,211,153,0.2)', 'count' => '3,420'],
            ['name' => 'Mythology', 'slug' => 'mythology', 'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#7C6CFF" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>', 'color' => 'rgba(124,108,255,0.15)', 'glow' => 'rgba(124,108,255,0.2)', 'count' => '1,680'],
            ['name' => 'Zodiac', 'slug' => 'zodiac', 'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/></svg>', 'color' => 'rgba(245,158,11,0.15)', 'glow' => 'rgba(245,158,11,0.2)', 'count' => '840'],
            ['name' => 'Civilizations', 'slug' => 'civilizations', 'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#EC4899" stroke-width="1.5"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4"/><line x1="9" y1="10" x2="9" y2="10.01"/><line x1="15" y1="10" x2="15" y2="10.01"/><line x1="9" y1="14" x2="9" y2="14.01"/><line x1="15" y1="14" x2="15" y2="14.01"/></svg>', 'color' => 'rgba(236,72,153,0.15)', 'glow' => 'rgba(236,72,153,0.2)', 'count' => '2,310'],
            ['name' => 'Technology', 'slug' => 'technology', 'icon' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#22F2E2" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>', 'color' => 'rgba(34,242,226,0.15)', 'glow' => 'rgba(34,242,226,0.2)', 'count' => '4,120'],
        ];
        @endphp

        @foreach($domains as $i => $d)
        <a href="{{ route('encyclopedia.index', ['category' => $d['slug']]) }}" class="domain-card reveal" style="--card-color:{{ $d['color'] }};--card-glow:{{ $d['glow'] }};--icon-bg:{{ $d['color'] }};">
            <div class="domain-icon">{!! $d['icon'] !!}</div>
            <span class="domain-name">{{ $d['name'] }}</span>
            <span class="domain-count">{{ $d['count'] }} articles</span>
        </a>
        @endforeach
    </div>
</section>

{{-- ═══════════════════════════════════════════
     TRENDING KNOWLEDGE
═══════════════════════════════════════════ --}}
<section style="max-width:1280px;margin:0 auto;padding:5rem 1.5rem;">
    <div class="section-header">
        <div class="section-label">Trending Now</div>
        <h2 class="section-title">Trending Knowledge</h2>
        <p class="section-desc">The most explored topics across the Curevia universe right now.</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.5rem;">
        @foreach($articles->take(7) as $item)
        <a href="{{ route('encyclopedia.show', $item->slug) }}" class="trending-card reveal">
            <div style="overflow:hidden;">
                <img src="{{ $item->featured_image }}" alt="{{ $item->title }}" loading="lazy" width="600" height="400">
            </div>
            <div class="trending-card-body">
                <div class="trending-badge">{{ $item->category->name }}</div>
                <h3 style="font-size:1.15rem;font-weight:700;color:var(--text-primary);margin:0.5rem 0 0.4rem;">{{ $item->title }}</h3>
                <p style="font-size:0.85rem;color:var(--text-secondary);line-height:1.6;">{{ $item->summary }}</p>
            </div>
        </a>
        @endforeach
    </div>

    <div style="text-align:center;margin-top:3rem;">
        <a href="{{ route('encyclopedia.index') }}" class="btn-secondary">
            View All Topics
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     FEATURED STORIES — LADDER LAYOUT
═══════════════════════════════════════════ --}}
<section style="max-width:1100px;margin:0 auto;padding:5rem 1.5rem;">
    <div class="section-header">
        <div class="section-label">Featured Stories</div>
        <h2 class="section-title">Deep Dives & Discoveries</h2>
        <p class="section-desc">Long-form explorations into the most fascinating topics on Earth and beyond.</p>
    </div>

    @php
    $homeStories = $stories;
    @endphp

    @foreach($homeStories as $i => $story)
    <div class="story-card-ladder {{ $i % 2 !== 0 ? 'reverse' : '' }} reveal">
        <div class="story-img-wrapper">
            <img src="{{ $story->featured_image }}" alt="{{ $story->title }}" loading="lazy" width="700" height="438">
        </div>
        <div>
            <div class="story-number">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</div>
            <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;">
                <span class="trending-badge">{{ $story->category->name }}</span>
                <span style="font-size:0.75rem;color:var(--text-muted);">{{ $story->read_time }} min read</span>
            </div>
            <h3 style="font-size:1.5rem;font-weight:800;color:var(--text-primary);line-height:1.3;margin-bottom:0.75rem;">{{ $story->title }}</h3>
            <p style="color:var(--text-secondary);font-size:0.95rem;line-height:1.7;margin-bottom:1.25rem;">{{ $story->excerpt }}</p>
            <a href="{{ route('stories.show', $story->slug) }}" class="btn-secondary" style="padding:0.6rem 1.5rem;font-size:0.85rem;">
                Read Story
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
    @endforeach

    <div style="text-align:center;margin-top:3rem;">
        <a href="{{ route('stories.index') }}" class="btn-primary">
            Explore All Stories
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     MID-PAGE AD
═══════════════════════════════════════════ --}}
<div style="max-width:1280px;margin:2rem auto;padding:0 1.5rem;">
    <div class="ad-banner" style="padding:2rem;">
        <span style="opacity:0.5;">SPONSORED</span> — Premium placement for knowledge brands — <a href="#" style="color:var(--accent-cyan);text-decoration:none;">Learn More</a>
    </div>
</div>

{{-- ═══════════════════════════════════════════
     KNOWLEDGE MARKETPLACE
═══════════════════════════════════════════ --}}
<section style="max-width:1280px;margin:0 auto;padding:5rem 1.5rem;">
    <div class="section-header">
        <div class="section-label">Knowledge Marketplace</div>
        <h2 class="section-title">Tools for Curious Minds</h2>
        <p class="section-desc">Handpicked products for explorers, learners, and knowledge seekers.</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1.5rem;">
        @foreach($products as $p)
        <div class="product-card reveal">
            <div style="overflow:hidden;position:relative;">
                <img src="{{ $p->image }}" alt="{{ $p->name }}" class="product-img" loading="lazy" width="500" height="400">
                @if($p->badge)
                <span class="product-badge">{{ $p->badge }}</span>
                @endif
            </div>
            <div style="padding:1.25rem;">
                <span style="font-size:0.7rem;color:var(--accent-violet);font-weight:600;text-transform:uppercase;letter-spacing:0.08em;">{{ $p->category }}</span>
                <h4 style="font-size:0.95rem;font-weight:700;color:var(--text-primary);margin:0.4rem 0;line-height:1.4;">{{ $p->name }}</h4>
                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.75rem;">
                    <div class="stars-rating">
                        @for($s = 1; $s <= 5; $s++)
                        <svg class="star-icon" width="14" height="14" viewBox="0 0 24 24" fill="{{ $s <= floor($p->rating) ? '#F59E0B' : 'none' }}" stroke="#F59E0B" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        @endfor
                    </div>
                    <span style="font-size:0.75rem;color:var(--text-muted);">{{ $p->rating }}</span>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:1.25rem;font-weight:800;color:var(--accent-cyan);">${{ number_format($p->price, 2) }}</span>
                    <a href="{{ route('shop.show', $p->slug) }}" class="add-to-cart-btn" style="text-decoration:none;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        View
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div style="text-align:center;margin-top:3rem;">
        <a href="{{ route('shop.index') }}" class="btn-violet">
            Visit Marketplace
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     CONTRIBUTOR SPOTLIGHT
═══════════════════════════════════════════ --}}
<section style="max-width:1280px;margin:0 auto;padding:5rem 1.5rem;" id="contributors">
    <div class="section-header">
        <div class="section-label">Contributor Spotlight</div>
        <h2 class="section-title">Meet Our Knowledge Experts</h2>
        <p class="section-desc">The brilliant minds behind Curevia's growing encyclopedia of knowledge.</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:1.5rem;">
        @foreach($contributors as $c)
        <div class="contributor-card reveal">
            <img src="{{ $c->avatar }}" alt="{{ $c->user->name }}" class="contributor-avatar" loading="lazy" width="150" height="150">
            <h4 style="font-size:1.05rem;font-weight:700;color:var(--text-primary);margin-bottom:0.25rem;">{{ $c->user->name }}</h4>
            <p style="font-size:0.8rem;color:var(--accent-cyan);font-weight:500;margin-bottom:0.75rem;">{{ $c->expertise }}</p>
            <div style="display:flex;align-items:center;justify-content:center;gap:1.5rem;margin-bottom:1rem;">
                <div style="text-align:center;">
                    <div style="font-size:1.1rem;font-weight:800;color:var(--text-primary);">{{ $c->user->articles_count ?? 0 }}</div>
                    <div style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;">Articles</div>
                </div>
                <div style="width:1px;height:24px;background:var(--border-subtle);"></div>
                <div style="text-align:center;">
                    <div style="font-size:1.1rem;font-weight:800;color:var(--text-primary);">{{ number_format($c->reputation) }}</div>
                    <div style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;">Reputation</div>
                </div>
            </div>
            <span class="reputation-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                Top Contributor
            </span>
        </div>
        @endforeach
    </div>
</section>

{{-- ═══════════════════════════════════════════
     CALL TO ACTION
═══════════════════════════════════════════ --}}
<section style="max-width:900px;margin:0 auto;padding:6rem 1.5rem;text-align:center;">
    <div class="reveal">
        <div class="section-label">Join Curevia</div>
        <h2 class="section-title" style="margin-bottom:1rem;">Begin Your Journey Across<br><span style="background:linear-gradient(135deg,var(--accent-cyan),var(--accent-violet));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">The Ocean of Knowledge</span></h2>
        <p class="section-desc" style="margin-bottom:2.5rem;">Create a free account to bookmark articles, follow topics, contribute knowledge, and unlock the full Curevia experience.</p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
            <a href="{{ route('register') }}" class="btn-primary" style="padding:1rem 2.5rem;font-size:1rem;">
                Get Started Free
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
            <a href="{{ route('encyclopedia.index') }}" class="btn-secondary" style="padding:1rem 2.5rem;font-size:1rem;">
                Browse Encyclopedia
            </a>
        </div>
    </div>
</section>

@endsection
