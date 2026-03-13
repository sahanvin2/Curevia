@extends('layouts.app')

@section('title', $article->title . ' | Curevia Encyclopedia')
@section('meta_description', $article->summary)
@section('og_type', 'article')
@section('og_image', $article->featured_image)

@section('schema_markup')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Article",
    "headline": "{{ $article->title }}",
    "description": "{{ $article->summary }}",
    "image": "{{ $article->featured_image }}",
    "author": { "@@type": "Person", "name": "{{ $article->author->name ?? 'Curevia' }}" },
    "publisher": { "@@type": "Organization", "name": "Curevia" },
    "datePublished": "{{ $article->published_at?->toIso8601String() }}",
    "dateModified": "{{ $article->updated_at->toIso8601String() }}"
}
</script>
@endsection

@section('content')

{{-- Article Hero --}}
<section class="article-hero">
    <img src="{{ $article->featured_image }}" alt="{{ $article->title }}" width="1200" height="675">
    <div class="article-hero-overlay"></div>
    <div style="position:absolute;bottom:3rem;left:0;right:0;z-index:2;max-width:900px;margin:0 auto;padding:0 1.5rem;">
        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem;">
            <span class="trending-badge">{{ $article->category->name }}</span>
            <span style="font-size:0.8rem;color:var(--text-muted);">{{ $article->read_time }} min read</span>
            <span style="font-size:0.8rem;color:var(--text-muted);">•</span>
            <span style="font-size:0.8rem;color:var(--text-muted);">Updated {{ $article->updated_at->format('M j, Y') }}</span>
        </div>
        <h1 style="font-size:clamp(2rem,5vw,3.5rem);font-weight:900;color:var(--text-primary);line-height:1.1;letter-spacing:-0.03em;">{{ $article->title }}</h1>
    </div>
</section>

{{-- Breadcrumb --}}
<div style="max-width:1100px;margin:1.5rem auto 0;padding:0 1.5rem;">
    <nav style="display:flex;align-items:center;gap:0.5rem;font-size:0.8rem;color:var(--text-muted);">
        <a href="{{ route('home') }}" style="color:var(--text-muted);text-decoration:none;transition:color .2s;" onmouseover="this.style.color='var(--accent-cyan)'" onmouseout="this.style.color='var(--text-muted)'">Home</a>
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        <a href="{{ route('encyclopedia.index') }}" style="color:var(--text-muted);text-decoration:none;transition:color .2s;" onmouseover="this.style.color='var(--accent-cyan)'" onmouseout="this.style.color='var(--text-muted)'">Encyclopedia</a>
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        <a href="{{ route('encyclopedia.index', ['category' => $article->category->slug]) }}" style="color:var(--text-muted);text-decoration:none;transition:color .2s;" onmouseover="this.style.color='var(--accent-cyan)'" onmouseout="this.style.color='var(--text-muted)'">{{ $article->category->name }}</a>
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        <span style="color:var(--accent-cyan);">{{ $article->title }}</span>
    </nav>
</div>

{{-- Article Layout --}}
<div style="max-width:1100px;margin:2rem auto 4rem;padding:0 1.5rem;display:grid;grid-template-columns:1fr 320px;gap:3rem;" class="article-layout-grid">

    {{-- Main Content --}}
    <article class="article-body">

        {{-- Summary --}}
        <div style="background:rgba(34,242,226,0.04);border:1px solid rgba(34,242,226,0.12);border-radius:1rem;padding:1.5rem;margin-bottom:2rem;">
            <p style="color:var(--text-secondary);font-size:1.05rem;line-height:1.8;margin:0;">
                {{ $article->summary }}
            </p>
        </div>

        {{-- Video Embed (if article has a video) --}}
        @if($article->video_url)
        @php
            $mainVideoUrl = $article->video_url;
            $isMainEmbed = str_contains($mainVideoUrl, 'youtube.com') || str_contains($mainVideoUrl, 'youtu.be') || str_contains($mainVideoUrl, 'vimeo.com');
        @endphp
        <div style="margin-bottom:2.5rem;">
            <h4 style="font-size:0.8rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--accent-violet);margin-bottom:0.75rem;display:flex;align-items:center;gap:0.5rem;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8" fill="currentColor"/></svg>
                Watch: {{ $article->title }} Explained
            </h4>
            <div style="position:relative;width:100%;padding-top:56.25%;border-radius:1rem;overflow:hidden;border:1px solid var(--border-subtle);background:#000;">
                @if($isMainEmbed)
                <iframe src="{{ $mainVideoUrl }}?modestbranding=1&rel=0&color=white" title="{{ $article->title }} video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="position:absolute;top:0;left:0;width:100%;height:100%;"></iframe>
                @else
                <video controls style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;">
                    <source src="{{ $mainVideoUrl }}">
                </video>
                @endif
            </div>
        </div>
        @endif

        {{-- Lightbox Modal --}}
        @if($article->images && count($article->images) > 0)
        <div id="lightbox-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.92);backdrop-filter:blur(12px);justify-content:center;align-items:center;" onclick="if(event.target===this)closeLightbox()">
            <button onclick="closeLightbox()" style="position:absolute;top:1.5rem;right:1.5rem;background:none;border:none;color:white;cursor:pointer;z-index:10;padding:0.5rem;" aria-label="Close">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <button onclick="lightboxPrev()" style="position:absolute;left:1.5rem;top:50%;transform:translateY(-50%);background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);border-radius:50%;width:48px;height:48px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:white;z-index:10;" aria-label="Previous">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <button onclick="lightboxNext()" style="position:absolute;right:1.5rem;top:50%;transform:translateY(-50%);background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);border-radius:50%;width:48px;height:48px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:white;z-index:10;" aria-label="Next">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
            </button>
            <img id="lightbox-img" src="" alt="" style="max-width:90vw;max-height:85vh;object-fit:contain;border-radius:0.75rem;">
            <div id="lightbox-counter" style="position:absolute;bottom:1.5rem;left:50%;transform:translateX(-50%);color:rgba(255,255,255,0.6);font-size:0.85rem;font-weight:500;"></div>
        </div>
        @endif

        {{-- Table of Contents --}}
        @if($article->content_sections && count($article->content_sections) > 0)
        <div style="background:rgba(17,24,39,0.6);border:1px solid var(--border-subtle);border-radius:1rem;padding:1.25rem 1.5rem;margin-bottom:2.5rem;">
            <h4 style="font-size:0.8rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--accent-cyan);margin-bottom:0.75rem;">Table of Contents</h4>
            <ol style="list-style:none;counter-reset:toc;padding:0;margin:0;">
                @foreach($article->content_sections as $idx => $section)
                <li style="counter-increment:toc;margin-bottom:0.4rem;">
                    <a href="#section-{{ $idx }}" style="color:var(--text-secondary);text-decoration:none;font-size:0.875rem;display:flex;align-items:center;gap:0.5rem;padding:0.25rem 0;transition:color .2s;" onmouseover="this.style.color='var(--accent-cyan)'" onmouseout="this.style.color='var(--text-secondary)'">
                        <span style="font-size:0.7rem;color:var(--text-muted);min-width:20px;">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        {{ $section['title'] }}
                    </a>
                </li>
                @endforeach
            </ol>
        </div>
        @endif

        {{-- Content Sections --}}
        @if($article->content_sections && count($article->content_sections) > 0)
            @php $allImages = $article->images ?? []; @endphp
            @foreach($article->content_sections as $idx => $section)
            <section id="section-{{ $idx }}">
                <h2>{{ $section['title'] }}</h2>
                @php $paragraphs = array_values(array_filter(array_map('trim', explode("\n\n", $section['body'])))); @endphp

                {{-- First paragraph --}}
                @if(isset($paragraphs[0]))
                <p>{{ $paragraphs[0] }}</p>
                @endif

                {{-- Section images (new format) with legacy fallback to images array --}}
                @php
                    $sectionImages = is_array($section['images'] ?? null) ? $section['images'] : [];
                    $imgIndex = $idx + 1;
                @endphp
                @if(!empty($sectionImages))
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:0.6rem;margin:1.3rem 0;">
                    @foreach($sectionImages as $secImg)
                    <figure style="margin:0;border-radius:0.7rem;overflow:hidden;border:1px solid var(--border-subtle);">
                        <img src="{{ $secImg }}" alt="{{ $section['title'] }}" loading="lazy" style="width:100%;height:150px;object-fit:cover;display:block;">
                    </figure>
                    @endforeach
                </div>
                @elseif(isset($allImages[$imgIndex]))
                <figure style="margin:1.5rem 0;border-radius:0.875rem;overflow:hidden;border:1px solid var(--border-subtle);cursor:pointer;" onclick="openLightbox({{ $imgIndex }})">
                    <img src="{{ $allImages[$imgIndex] }}" alt="{{ $section['title'] }}" loading="lazy" style="width:100%;max-height:340px;object-fit:cover;display:block;transition:transform .4s ease;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                    <figcaption style="padding:0.6rem 1rem;font-size:0.78rem;color:var(--text-muted);background:rgba(17,24,39,0.5);text-align:center;">{{ $section['title'] }}</figcaption>
                </figure>
                @endif

                @php
                    $sectionVideoUrl = $section['video_url'] ?? null;
                    $isSectionEmbed = $sectionVideoUrl && (str_contains($sectionVideoUrl, 'youtube.com') || str_contains($sectionVideoUrl, 'youtu.be') || str_contains($sectionVideoUrl, 'vimeo.com'));
                @endphp
                @if($sectionVideoUrl)
                <div style="position:relative;width:100%;padding-top:56.25%;border-radius:0.9rem;overflow:hidden;border:1px solid var(--border-subtle);background:#000;margin:1.1rem 0 1.4rem;">
                    @if($isSectionEmbed)
                    <iframe src="{{ $sectionVideoUrl }}" title="{{ $section['title'] }} video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="position:absolute;top:0;left:0;width:100%;height:100%;"></iframe>
                    @else
                    <video controls style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;">
                        <source src="{{ $sectionVideoUrl }}">
                    </video>
                    @endif
                </div>
                @endif

                {{-- Remaining paragraphs --}}
                @for($pIdx = 1; $pIdx < count($paragraphs); $pIdx++)
                <p>{{ $paragraphs[$pIdx] }}</p>
                @endfor
            </section>
            @endforeach
        @else
            {{-- Fallback to raw content --}}
            <div>{!! nl2br(e($article->content)) !!}</div>
        @endif

        {{-- Related Topics --}}
        @if($related->count() > 0)
        <section style="margin-top:3rem;">
            <h2>Related Topics</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.25rem;margin-top:1.25rem;">
                @foreach($related as $rt)
                <a href="{{ route('encyclopedia.show', $rt->slug) }}" class="glass-card" style="text-decoration:none;overflow:hidden;transition:transform .3s ease,box-shadow .3s ease;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 32px rgba(0,0,0,0.3)'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">
                    <div style="position:relative;overflow:hidden;">
                        <img src="{{ $rt->featured_image }}" alt="{{ $rt->title }}" style="width:100%;height:140px;object-fit:cover;transition:transform .5s ease;" loading="lazy" width="400" height="250" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                        <span style="position:absolute;top:0.5rem;left:0.5rem;font-size:0.65rem;font-weight:600;color:var(--accent-cyan);background:rgba(11,15,20,0.75);backdrop-filter:blur(8px);padding:0.25rem 0.6rem;border-radius:2rem;text-transform:uppercase;letter-spacing:0.05em;">{{ $rt->category->name ?? '' }}</span>
                    </div>
                    <div style="padding:1rem;">
                        <h4 style="font-size:0.9rem;font-weight:700;color:var(--text-primary);margin-bottom:0.35rem;line-height:1.4;">{{ $rt->title }}</h4>
                        <p style="font-size:0.78rem;color:var(--text-muted);line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ Str::limit($rt->summary, 80) }}</p>
                        <div style="display:flex;align-items:center;gap:0.75rem;margin-top:0.6rem;">
                            <span style="font-size:0.7rem;color:var(--text-muted);">{{ $rt->read_time }} min read</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Actions --}}
        <div style="display:flex;align-items:center;gap:1rem;margin-top:3rem;padding-top:2rem;border-top:1px solid var(--border-subtle);">
            <button onclick="toggleBookmark(this)" class="btn-secondary" style="padding:0.6rem 1.25rem;font-size:0.8rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                Bookmark
            </button>
            <button class="btn-secondary" style="padding:0.6rem 1.25rem;font-size:0.8rem;display:flex;align-items:center;gap:0.5rem;" onclick="openShareModal(document.title, location.href, 'article')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.59 13.51l6.83 3.98M15.41 6.51l-6.82 3.98"/></svg>
                Share
            </button>
        </div>
    </article>

    {{-- Sidebar --}}
    <aside class="article-sidebar-col">

        {{-- Quick Facts --}}
        <div class="quick-facts-card">
            <h3 style="font-size:0.85rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--accent-cyan);margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                Quick Facts
            </h3>
            @if($article->quick_facts && count($article->quick_facts) > 0)
                @foreach($article->quick_facts as $label => $value)
                <div class="quick-fact-item">
                    <span class="fact-label">{{ $label }}</span>
                    <span class="fact-value">{{ $value }}</span>
                </div>
                @endforeach
            @endif
            <div class="quick-fact-item" style="margin-top:0.25rem;border-top:1px solid var(--border-glow);padding-top:0.75rem;">
                <span class="fact-label">Category</span>
                <a href="{{ route('encyclopedia.index', ['category' => $article->category->slug]) }}" class="fact-value" style="color:var(--accent-cyan);text-decoration:none;font-weight:600;">{{ $article->category->name }}</a>
            </div>
            <div class="quick-fact-item">
                <span class="fact-label">Read Time</span>
                <span class="fact-value">{{ $article->read_time }} min read</span>
            </div>
            <div class="quick-fact-item">
                <span class="fact-label">Author</span>
                <span class="fact-value">{{ $article->author->name ?? 'Curevia Team' }}</span>
            </div>
        </div>
    </aside>
</div>

{{-- Related Products from the Knowledge Shop --}}
@if(isset($relatedProducts) && $relatedProducts->count() > 0)
<section style="max-width:1100px;margin:0 auto 4rem;padding:0 1.5rem;">

    {{-- Section header --}}
    <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:1.75rem;flex-wrap:wrap;gap:1rem;">
        <div>
            <span style="font-size:0.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.14em;color:#F59E0B;display:block;margin-bottom:0.4rem;">Knowledge Shop</span>
            <h2 style="font-size:1.5rem;font-weight:900;color:var(--text-primary);line-height:1.2;margin:0;">
                Products Related to
                <span style="background:linear-gradient(135deg,var(--accent-cyan),var(--accent-violet));-webkit-background-clip:text;-webkit-text-fill-color:transparent;">{{ $article->category->name }}</span>
            </h2>
        </div>
        <a href="{{ route('shop.index') }}" style="display:inline-flex;align-items:center;gap:0.4rem;font-size:0.83rem;font-weight:700;color:var(--accent-cyan);text-decoration:none;white-space:nowrap;padding:0.5rem 1.1rem;border:1px solid rgba(34,242,226,0.2);border-radius:100px;transition:all .2s;" onmouseover="this.style.background='rgba(34,242,226,0.06)'" onmouseout="this.style.background='none'">
            Browse All Products
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
    </div>

    {{-- Product cards grid --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:1.25rem;">
        @foreach($relatedProducts as $rp)
        @php
            $mktName  = 'View Product';
            $mktColor = 'var(--accent-cyan)';
            $mktBg    = 'rgba(34,242,226,0.08)';
            $mktBorder= 'rgba(34,242,226,0.25)';
            if ($rp->affiliate_url) {
                if (str_contains($rp->affiliate_url, 'amazon')) {
                    $mktName = 'Buy on Amazon'; $mktColor = '#FF9900'; $mktBg = 'rgba(255,153,0,0.08)'; $mktBorder = 'rgba(255,153,0,0.3)';
                } elseif (str_contains($rp->affiliate_url, 'aliexpress')) {
                    $mktName = 'Buy on AliExpress'; $mktColor = '#FF4747'; $mktBg = 'rgba(255,71,71,0.08)'; $mktBorder = 'rgba(255,71,71,0.3)';
                } elseif (str_contains($rp->affiliate_url, 'ebay')) {
                    $mktName = 'Buy on eBay'; $mktColor = '#0064D3'; $mktBg = 'rgba(0,100,211,0.08)'; $mktBorder = 'rgba(0,100,211,0.3)';
                } else {
                    $mktName = 'Shop Now';
                }
            }
            $buyHref   = $rp->affiliate_url ?: route('shop.show', $rp->slug);
            $buyTarget = $rp->affiliate_url ? '_blank' : '_self';
        @endphp
        <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;overflow:hidden;display:flex;flex-direction:column;transition:transform .2s,border-color .2s;" onmouseover="this.style.transform='translateY(-3px)';this.style.borderColor='rgba(34,242,226,0.2)'" onmouseout="this.style.transform='none';this.style.borderColor='var(--border-subtle)'">
            {{-- Image --}}
            <a href="{{ route('shop.show', $rp->slug) }}" style="display:block;aspect-ratio:4/3;overflow:hidden;background:rgba(11,15,20,0.5);flex-shrink:0;">
                <img src="{{ $rp->image }}" alt="{{ $rp->name }}" loading="lazy" style="width:100%;height:100%;object-fit:cover;transition:transform .4s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='none'">
            </a>
            {{-- Body --}}
            <div style="padding:1rem;display:flex;flex-direction:column;flex:1;">
                <span style="font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--accent-violet);margin-bottom:0.35rem;">{{ $rp->category }}</span>
                <a href="{{ route('shop.show', $rp->slug) }}" style="text-decoration:none;">
                    <h4 style="font-size:0.88rem;font-weight:700;color:var(--text-primary);line-height:1.4;margin:0 0 0.5rem;">{{ Str::limit($rp->name, 55) }}</h4>
                </a>
                {{-- Rating --}}
                <div style="display:flex;align-items:center;gap:0.3rem;margin-bottom:0.6rem;">
                    @for($s=1;$s<=5;$s++)<svg width="11" height="11" viewBox="0 0 24 24" fill="{{ $s<=round($rp->rating)?'#F59E0B':'none' }}" stroke="#F59E0B" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>@endfor
                    <span style="font-size:0.72rem;color:var(--text-muted);margin-left:0.2rem;">({{ number_format($rp->reviews_count) }})</span>
                </div>
                {{-- Price --}}
                <div style="display:flex;align-items:baseline;gap:0.5rem;margin-bottom:0.85rem;margin-top:auto;">
                    <span style="font-size:1.1rem;font-weight:900;color:var(--accent-cyan);">${{ number_format($rp->price, 2) }}</span>
                    @if($rp->original_price && $rp->original_price > $rp->price)
                    <span style="font-size:0.8rem;color:var(--text-muted);text-decoration:line-through;">${{ number_format($rp->original_price, 2) }}</span>
                    <span style="font-size:0.7rem;font-weight:700;color:#34D399;">-{{ round((($rp->original_price-$rp->price)/$rp->original_price)*100) }}%</span>
                    @endif
                </div>
                {{-- Buy Now button --}}
                <a href="{{ $buyHref }}" target="{{ $buyTarget }}" rel="{{ $buyTarget==='_blank' ? 'noopener noreferrer' : '' }}"
                    style="display:flex;align-items:center;justify-content:center;gap:0.4rem;padding:0.6rem 0.75rem;background:{{ $mktBg }};border:1px solid {{ $mktBorder }};border-radius:0.6rem;color:{{ $mktColor }};font-size:0.78rem;font-weight:700;text-decoration:none;transition:all .2s;letter-spacing:0.01em;"
                    onmouseover="this.style.filter='brightness(1.2)'" onmouseout="this.style.filter='none'">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    {{ $mktName }}
                </a>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Footer note --}}
    <p style="text-align:center;font-size:0.75rem;color:var(--text-muted);margin-top:1.25rem;">
        Items sold by partner retailers. Curevia may earn a commission on purchases. <a href="{{ route('shop.index') }}" style="color:var(--accent-cyan);text-decoration:none;">Learn more →</a>
    </p>
</section>
@endif

@endsection

@section('extra_head')
<style>
@media (max-width: 900px) {
    .article-layout-grid { grid-template-columns: 1fr !important; }
    .article-sidebar-col { order: -1; }
}
.zoom-icon { opacity: 0; }
div:hover > .zoom-icon { opacity: 1 !important; }
</style>
@endsection

@section('extra_scripts')
<script>
(function() {
    const images = @json($article->images ? array_values($article->images) : []);
    let currentIdx = 0;

    window.openLightbox = function(idx) {
        if (!images.length) return;
        currentIdx = idx;
        const modal = document.getElementById('lightbox-modal');
        const img = document.getElementById('lightbox-img');
        const counter = document.getElementById('lightbox-counter');
        if (!modal) return;
        img.src = images[currentIdx];
        counter.textContent = (currentIdx + 1) + ' / ' + images.length;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    window.closeLightbox = function() {
        const modal = document.getElementById('lightbox-modal');
        if (modal) modal.style.display = 'none';
        document.body.style.overflow = '';
    };

    window.lightboxNext = function() {
        currentIdx = (currentIdx + 1) % images.length;
        document.getElementById('lightbox-img').src = images[currentIdx];
        document.getElementById('lightbox-counter').textContent = (currentIdx + 1) + ' / ' + images.length;
    };

    window.lightboxPrev = function() {
        currentIdx = (currentIdx - 1 + images.length) % images.length;
        document.getElementById('lightbox-img').src = images[currentIdx];
        document.getElementById('lightbox-counter').textContent = (currentIdx + 1) + ' / ' + images.length;
    };

    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('lightbox-modal');
        if (!modal || modal.style.display === 'none') return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowRight') lightboxNext();
        if (e.key === 'ArrowLeft') lightboxPrev();
    });
})();
</script>
@endsection
