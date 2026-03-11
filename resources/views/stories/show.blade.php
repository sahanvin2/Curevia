@extends('layouts.app')

@section('title', $story->title . ' | Curevia Stories')
@section('meta_description', $story->excerpt)
@section('og_type', 'article')

@section('content')

{{-- Story Hero --}}
<section class="article-hero">
    <img src="{{ $story->featured_image }}" alt="{{ $story->title }}" width="1200" height="675">
    <div class="article-hero-overlay"></div>
    <div style="position:absolute;bottom:3rem;left:0;right:0;z-index:2;max-width:800px;margin:0 auto;padding:0 1.5rem;">
        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem;">
            <span class="trending-badge">{{ $story->category->name }}</span>
            <span style="font-size:0.8rem;color:var(--text-muted);">{{ $story->read_time }} min read</span>
        </div>
        <h1 style="font-size:clamp(2rem,5vw,3rem);font-weight:900;color:var(--text-primary);line-height:1.1;letter-spacing:-0.02em;margin-bottom:1rem;">{{ $story->title }}</h1>
        <div style="display:flex;align-items:center;gap:1rem;">
            @if($story->author)
            <img src="{{ $story->author->contributorProfile->avatar ?? 'https://i.pravatar.cc/80?img=1' }}" alt="{{ $story->author->name }}" style="width:40px;height:40px;border-radius:50%;border:1.5px solid rgba(34,242,226,0.3);" width="80" height="80">
            <div>
                <span style="font-size:0.85rem;font-weight:600;color:var(--text-primary);">{{ $story->author->name }}</span>
                <span style="font-size:0.75rem;color:var(--text-muted);display:block;">Published {{ $story->published_at?->format('M j, Y') }}</span>
            </div>
            @endif
        </div>
    </div>
</section>

{{-- Breadcrumb --}}
<div style="max-width:800px;margin:1.5rem auto 0;padding:0 1.5rem;">
    <nav style="display:flex;align-items:center;gap:0.5rem;font-size:0.8rem;color:var(--text-muted);">
        <a href="{{ route('home') }}" style="color:var(--text-muted);text-decoration:none;">Home</a>
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        <a href="{{ route('stories.index') }}" style="color:var(--text-muted);text-decoration:none;">Stories</a>
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        <span style="color:var(--accent-cyan);">{{ $story->title }}</span>
    </nav>
</div>

{{-- Story Content --}}
<article class="article-body" style="max-width:800px;margin:2rem auto 4rem;padding:0 1.5rem;">

    <p style="font-size:1.15rem;color:var(--text-secondary);line-height:1.9;margin-bottom:2rem;">
        {{ $story->excerpt }}
    </p>

    @foreach(explode("\n\n", $story->content) as $idx => $paragraph)
        @if(trim($paragraph))
        <p>{{ trim($paragraph) }}</p>
        @endif

        @if($idx === 1)
        <div class="ad-banner" style="margin:2rem 0;">
            <span style="opacity:0.5;">SPONSORED</span> — Continue your learning journey — <a href="#" style="color:var(--accent-cyan);text-decoration:none;">Explore resources</a>
        </div>
        @endif
    @endforeach

    {{-- Related Stories --}}
    @if($related->count() > 0)
    <div style="margin-top:3rem;padding-top:2rem;border-top:1px solid var(--border-subtle);">
        <h3 style="font-size:1.1rem;font-weight:700;color:var(--text-primary);margin-bottom:1.25rem;">Continue Reading</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;">
            @foreach($related as $r)
            <a href="{{ route('stories.show', $r->slug) }}" class="glass-card" style="text-decoration:none;overflow:hidden;">
                <img src="{{ $r->featured_image }}" alt="{{ $r->title }}" style="width:100%;height:100px;object-fit:cover;" loading="lazy" width="400" height="250">
                <div style="padding:0.75rem;"><h4 style="font-size:0.85rem;font-weight:700;color:var(--text-primary);">{{ $r->title }}</h4></div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Actions --}}
    <div style="display:flex;align-items:center;gap:1rem;margin-top:2rem;padding-top:1.5rem;border-top:1px solid var(--border-subtle);">
        <button onclick="toggleBookmark(this)" class="btn-secondary" style="padding:0.6rem 1.25rem;font-size:0.8rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
            Bookmark
        </button>
        <button class="btn-secondary" style="padding:0.6rem 1.25rem;font-size:0.8rem;display:flex;align-items:center;gap:0.5rem;" onclick="openShareModal(document.title, location.href, 'story')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.59 13.51l6.83 3.98M15.41 6.51l-6.82 3.98"/></svg>
            Share
        </button>
    </div>

    {{-- Related Products --}}
    @if(isset($relatedProducts) && $relatedProducts->count() > 0)
    <div style="margin-top:3rem;padding-top:2rem;border-top:1px solid var(--border-subtle);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.5rem;flex-wrap:wrap;gap:0.5rem;">
            <div>
                <h3 style="font-size:1.05rem;font-weight:800;color:var(--text-primary);margin:0;">Explore Related Products</h3>
                <p style="font-size:0.78rem;color:var(--text-muted);margin:0.25rem 0 0;">Curated picks based on this topic</p>
            </div>
            <a href="{{ route('shop.index') }}" style="font-size:0.8rem;color:var(--accent-cyan);text-decoration:none;display:flex;align-items:center;gap:0.3rem;white-space:nowrap;">
                Browse shop <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1rem;margin-top:1.25rem;">
            @foreach($relatedProducts as $rp)
            <a href="{{ route('shop.show', $rp->slug) }}" style="text-decoration:none;display:flex;flex-direction:column;overflow:hidden;border-radius:1rem;border:1px solid rgba(34,242,226,0.08);background:rgba(17,24,39,0.6);transition:all .25s;" onmouseover="this.style.borderColor='rgba(34,242,226,0.25)';this.style.transform='translateY(-3px)'" onmouseout="this.style.borderColor='rgba(34,242,226,0.08)';this.style.transform='none'">
                <div style="position:relative;overflow:hidden;">
                    <img src="{{ $rp->image }}" alt="{{ $rp->name }}" style="width:100%;height:130px;object-fit:cover;display:block;" loading="lazy">
                    @if($rp->original_price && $rp->original_price > $rp->price)
                    <span style="position:absolute;top:0.5rem;right:0.5rem;background:#EF4444;color:#fff;font-size:0.65rem;font-weight:800;padding:0.2rem 0.5rem;border-radius:0.4rem;">-{{ round((($rp->original_price - $rp->price) / $rp->original_price) * 100) }}%</span>
                    @endif
                </div>
                <div style="padding:0.8rem;flex:1;display:flex;flex-direction:column;gap:0.35rem;">
                    <span style="font-size:0.68rem;color:var(--accent-violet);font-weight:700;text-transform:uppercase;letter-spacing:0.07em;">{{ $rp->category }}</span>
                    <h4 style="font-size:0.82rem;font-weight:700;color:var(--text-primary);line-height:1.3;margin:0;">{{ Str::limit($rp->name, 50) }}</h4>
                    <div style="display:flex;align-items:center;gap:0.3rem;margin-top:auto;padding-top:0.35rem;">
                        <span style="font-size:0.92rem;font-weight:800;color:var(--accent-cyan);">${{ number_format($rp->price, 2) }}</span>
                        @if($rp->original_price && $rp->original_price > $rp->price)
                        <span style="font-size:0.72rem;color:var(--text-muted);text-decoration:line-through;">${{ number_format($rp->original_price, 2) }}</span>
                        @endif
                    </div>
                    <div style="display:flex;align-items:center;gap:0.25rem;">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="#F59E0B" stroke="#F59E0B" stroke-width="1"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <span style="font-size:0.72rem;color:var(--text-muted);">{{ $rp->rating }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</article>

@endsection
