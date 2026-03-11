@extends('layouts.app')

@section('title', $product->name . ' â€” Curevia Shop')
@section('meta_description', Str::limit($product->description, 160))

@section('content')
<section style="padding:7rem 0 5rem;">
    <div style="max-width:1280px;margin:0 auto;padding:0 1.5rem;">

        {{-- Breadcrumb --}}
        <nav style="margin-bottom:2.5rem;font-size:0.8rem;color:var(--text-muted);display:flex;align-items:center;gap:0.4rem;flex-wrap:wrap;">
            <a href="{{ route('home') }}" style="color:var(--text-muted);text-decoration:none;transition:color .2s;" onmouseover="this.style.color='var(--accent-cyan)'" onmouseout="this.style.color='var(--text-muted)'">Home</a>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="9 18 15 12 9 6"/></svg>
            <a href="{{ route('shop.index') }}" style="color:var(--text-muted);text-decoration:none;transition:color .2s;" onmouseover="this.style.color='var(--accent-cyan)'" onmouseout="this.style.color='var(--text-muted)'">Shop</a>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="9 18 15 12 9 6"/></svg>
            <a href="{{ route('shop.index', ['category' => $product->category]) }}" style="color:var(--text-muted);text-decoration:none;transition:color .2s;" onmouseover="this.style.color='var(--accent-cyan)'" onmouseout="this.style.color='var(--text-muted)'">{{ $product->category }}</a>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="9 18 15 12 9 6"/></svg>
            <span style="color:var(--text-secondary);">{{ Str::limit($product->name, 40) }}</span>
        </nav>

        {{-- Product Hero --}}
        <div class="pdp-grid">

            {{-- Left: Image Panel --}}
            <div class="pdp-image-panel">
                <div class="pdp-main-img-wrap" id="main-img-wrap">
                    <img src="{{ $product->image }}" alt="{{ $product->name }}" class="pdp-main-img" id="pdp-main-img">
                    @if($product->badge)
                    <span style="position:absolute;top:1rem;left:1rem;background:linear-gradient(135deg,var(--accent-cyan),var(--accent-violet));color:var(--bg-primary);padding:0.4rem 1rem;border-radius:2rem;font-size:0.72rem;font-weight:700;letter-spacing:0.04em;z-index:2;">{{ $product->badge }}</span>
                    @endif
                    @if($product->original_price && $product->original_price > $product->price)
                    <span style="position:absolute;top:1rem;right:1rem;background:#EF4444;color:white;padding:0.35rem 0.75rem;border-radius:0.5rem;font-size:0.8rem;font-weight:800;z-index:2;">-{{ round((($product->original_price - $product->price) / $product->original_price) * 100) }}%</span>
                    @endif
                    {{-- Zoom hint --}}
                    <div style="position:absolute;bottom:1rem;right:1rem;background:rgba(11,15,20,0.7);backdrop-filter:blur(8px);border-radius:0.5rem;padding:0.35rem 0.65rem;display:flex;align-items:center;gap:0.4rem;font-size:0.7rem;color:var(--text-muted);">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                        Hover to zoom
                    </div>
                </div>

                {{-- Thumbnails --}}
                @php $thumbImages = array_values(array_filter([$product->image])); @endphp
                @if(count($thumbImages) > 0)
                <div style="display:flex;gap:0.6rem;margin-top:0.75rem;flex-wrap:wrap;">
                    @foreach($thumbImages as $i => $img)
                    <button onclick="document.getElementById('pdp-main-img').src='{{ $img }}';document.querySelectorAll('.pdp-thumb').forEach(t=>t.classList.remove('active'));this.classList.add('active');" class="pdp-thumb {{ $i===0 ? 'active' : '' }}">
                        <img src="{{ $img }}" alt="View {{ $i+1 }}" style="width:100%;height:100%;object-fit:cover;">
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Right: Product Info --}}
            <div class="pdp-info">

                {{-- Category + badges row --}}
                <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;flex-wrap:wrap;">
                    <a href="{{ route('shop.index', ['category' => $product->category]) }}" style="font-size:0.72rem;color:var(--accent-violet);font-weight:700;text-transform:uppercase;letter-spacing:0.1em;text-decoration:none;background:rgba(124,108,255,0.1);padding:0.25rem 0.75rem;border-radius:100px;border:1px solid rgba(124,108,255,0.25);">{{ $product->category }}</a>
                    <span style="display:flex;align-items:center;gap:0.35rem;font-size:0.72rem;color:#34D399;background:rgba(52,211,153,0.1);padding:0.25rem 0.75rem;border-radius:100px;">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        In Stock
                    </span>
                </div>

                <h1 style="font-size:clamp(1.4rem,3vw,2rem);font-weight:900;color:var(--text-primary);margin-bottom:1rem;line-height:1.25;">{{ $product->name }}</h1>

                {{-- Rating Row --}}
                <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.5rem;padding-bottom:1.5rem;border-bottom:1px solid var(--border-subtle);">
                    <div style="display:flex;gap:2px;">
                        @for($s = 1; $s <= 5; $s++)
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="{{ $s <= round($product->rating) ? '#F59E0B' : 'rgba(245,158,11,0.2)' }}" stroke="#F59E0B" stroke-width="1"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        @endfor
                    </div>
                    <span style="font-size:0.93rem;color:#F59E0B;font-weight:700;">{{ $product->rating }}</span>
                    <span style="font-size:0.85rem;color:var(--text-muted);">{{ number_format($product->reviews_count) }} reviews</span>
                    <span style="font-size:0.8rem;color:var(--accent-cyan);cursor:pointer;text-decoration:underline;text-underline-offset:3px;">Read reviews</span>
                </div>

                {{-- Price Block --}}
                <div class="pdp-price-block">
                    <div style="display:flex;align-items:baseline;gap:1rem;flex-wrap:wrap;">
                        <span style="font-size:2.5rem;font-weight:900;color:var(--accent-cyan);line-height:1;">${{ number_format($product->price, 2) }}</span>
                        @if($product->original_price && $product->original_price > $product->price)
                        <span style="font-size:1.1rem;color:var(--text-muted);text-decoration:line-through;">${{ number_format($product->original_price, 2) }}</span>
                        <span style="font-size:0.85rem;background:rgba(52,211,153,0.15);color:#34D399;font-weight:700;padding:0.3rem 0.75rem;border-radius:0.5rem;border:1px solid rgba(52,211,153,0.25);">
                            Save ${{ number_format($product->original_price - $product->price, 2) }}
                        </span>
                        @endif
                    </div>
                    <div style="display:flex;align-items:center;gap:0.5rem;margin-top:0.75rem;font-size:0.82rem;color:#34D399;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                        Free Shipping on orders over $50
                    </div>
                </div>

                {{-- Short Description --}}
                <p style="color:var(--text-secondary);line-height:1.8;font-size:0.92rem;margin:1.5rem 0;">{{ $product->description }}</p>

                {{-- Features Checklist --}}
                @if($product->features && count($product->features) > 0)
                <div style="margin-bottom:1.75rem;">
                    <h3 style="font-size:0.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.75rem;">KEY FEATURES</h3>
                    <ul style="list-style:none;padding:0;display:grid;gap:0.5rem;">
                        @foreach($product->features as $feature)
                        <li style="display:flex;align-items:flex-start;gap:0.6rem;color:var(--text-secondary);font-size:0.88rem;line-height:1.5;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent-cyan)" stroke-width="2.5" stroke-linecap="round" style="flex-shrink:0;margin-top:2px;"><path d="M20 6L9 17l-5-5"/></svg>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Quantity + CTA --}}
                @php
                    $mktName   = 'View Product';
                    $mktSubtitle = 'Opens in new tab';
                    $mktColor  = 'var(--accent-cyan)';
                    $mktGrad   = 'linear-gradient(135deg,var(--accent-cyan),var(--accent-violet))';
                    $mktBadgeBg= 'rgba(34,242,226,0.08)';
                    $mktBadgeBorder = 'rgba(34,242,226,0.2)';
                    if ($product->affiliate_url) {
                        if (str_contains($product->affiliate_url, 'amazon')) {
                            $mktName = 'Buy on Amazon'; $mktSubtitle = 'Ships & handled by Amazon';
                            $mktColor = '#FF9900'; $mktGrad = 'linear-gradient(135deg,#FF9900,#FF6600)';
                            $mktBadgeBg = 'rgba(255,153,0,0.08)'; $mktBadgeBorder = 'rgba(255,153,0,0.3)';
                        } elseif (str_contains($product->affiliate_url, 'aliexpress')) {
                            $mktName = 'Buy on AliExpress'; $mktSubtitle = 'Ships from AliExpress seller';
                            $mktColor = '#FF4747'; $mktGrad = 'linear-gradient(135deg,#FF4747,#CC0000)';
                            $mktBadgeBg = 'rgba(255,71,71,0.08)'; $mktBadgeBorder = 'rgba(255,71,71,0.3)';
                        } elseif (str_contains($product->affiliate_url, 'ebay')) {
                            $mktName = 'Buy on eBay'; $mktSubtitle = 'Ships & handled by eBay';
                            $mktColor = '#0064D3'; $mktGrad = 'linear-gradient(135deg,#0064D3,#003985)';
                            $mktBadgeBg = 'rgba(0,100,211,0.08)'; $mktBadgeBorder = 'rgba(0,100,211,0.3)';
                        } else {
                            $mktName = 'Buy Now'; $mktSubtitle = 'Opens at partner store';
                        }
                    }
                    $buyHref   = $product->affiliate_url ?: null;
                    $buyTarget = $product->affiliate_url ? '_blank' : '';
                    $buyRel    = $product->affiliate_url ? 'noopener noreferrer' : '';
                @endphp

                {{-- Primary Buy Now CTA --}}
                @if($buyHref)
                <a href="{{ $buyHref }}" target="{{ $buyTarget }}" rel="{{ $buyRel }}"
                    style="display:flex;align-items:center;justify-content:center;gap:0.6rem;width:100%;padding:1rem 1.25rem;background:{{ $mktGrad }};border:none;border-radius:0.85rem;font-size:1.05rem;font-weight:800;color:#fff;text-decoration:none;cursor:pointer;transition:all .25s;box-shadow:0 4px 20px rgba(0,0,0,0.3);letter-spacing:0.01em;box-sizing:border-box;"
                    onmouseover="this.style.filter='brightness(1.1)';this.style.transform='translateY(-1px)'" onmouseout="this.style.filter='none';this.style.transform='none'">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    {{ $mktName }}
                </a>
                <p style="text-align:center;font-size:0.72rem;color:var(--text-muted);margin:0.4rem 0 0;">{{ $mktSubtitle }}</p>
                @else
                <button disabled style="display:flex;align-items:center;justify-content:center;gap:0.6rem;width:100%;padding:1rem 1.25rem;background:rgba(255,255,255,0.05);border:1px solid var(--border-subtle);border-radius:0.85rem;font-size:1.05rem;font-weight:800;color:var(--text-muted);cursor:not-allowed;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Link Not Available
                </button>
                <p style="text-align:center;font-size:0.72rem;color:var(--text-muted);margin:0.4rem 0 0;">Purchase link not yet set up.</p>
                @endif

                {{-- Marketplace source badge --}}
                <div style="display:flex;align-items:center;justify-content:center;gap:0.5rem;padding:0.6rem;background:{{ $mktBadgeBg }};border:1px solid {{ $mktBadgeBorder }};border-radius:0.6rem;margin-top:0.75rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="{{ $mktColor }}" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    @php
                    $soldBy = 'Partner Retailer';
                    if (str_contains($mktName, 'Amazon'))     $soldBy = 'Amazon';
                    elseif (str_contains($mktName, 'AliExpress')) $soldBy = 'AliExpress';
                    elseif (str_contains($mktName, 'eBay'))   $soldBy = 'eBay';
                    @endphp
                    <span style="font-size:0.75rem;color:{{ $mktColor }};font-weight:700;">Sold & Fulfilled by {{ $soldBy }}</span>
                </div>

                {{-- Trust badges row --}}
                <div style="display:flex;gap:1.5rem;padding-top:1.5rem;margin-top:1.25rem;border-top:1px solid var(--border-subtle);flex-wrap:wrap;">
                    <div style="display:flex;align-items:center;gap:0.4rem;font-size:0.78rem;color:var(--text-muted);">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--accent-cyan)" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Verified Retailer
                    </div>
                    <div style="display:flex;align-items:center;gap:0.4rem;font-size:0.78rem;color:var(--text-muted);">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--accent-violet)" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        Buyer Protection
                    </div>
                    <div style="display:flex;align-items:center;gap:0.4rem;font-size:0.78rem;color:var(--text-muted);">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="1.5"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                        Easy Returns
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs: Specs / Description / Reviews --}}
        <div class="pdp-tabs-wrap">
            <div class="pdp-tabs" id="pdp-tabs">
                @if($product->specifications && count($product->specifications) > 0)
                <button class="pdp-tab active" onclick="showTab('specs', this)">Specifications</button>
                @endif
                @if($product->long_description)
                <button class="pdp-tab {{ !($product->specifications && count($product->specifications) > 0) ? 'active' : '' }}" onclick="showTab('desc', this)">About This Product</button>
                @endif
                <button class="pdp-tab" onclick="showTab('reviews', this)">Reviews ({{ number_format($product->reviews_count) }})</button>
            </div>

            @if($product->specifications && count($product->specifications) > 0)
            <div class="pdp-tab-content active" id="tab-specs">
                <table style="width:100%;border-collapse:collapse;">
                    @foreach($product->specifications as $spec => $value)
                    <tr style="border-bottom:1px solid var(--border-subtle);">
                        <td style="padding:0.9rem 1rem;font-size:0.85rem;font-weight:600;color:var(--text-muted);width:35%;white-space:nowrap;">{{ $spec }}</td>
                        <td style="padding:0.9rem 1rem;font-size:0.875rem;color:var(--text-secondary);">{{ $value }}</td>
                    </tr>
                    @endforeach
                </table>
            </div>
            @endif

            @if($product->long_description)
            <div class="pdp-tab-content {{ !($product->specifications && count($product->specifications) > 0) ? 'active' : '' }}" id="tab-desc">
                <div style="color:var(--text-secondary);line-height:1.9;font-size:0.9rem;max-width:760px;">
                    @foreach(explode("\n\n", $product->long_description) as $para)
                    <p style="margin-bottom:1rem;">{{ $para }}</p>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="pdp-tab-content" id="tab-reviews">
                <div style="display:flex;align-items:center;gap:2rem;padding:1.5rem 0;flex-wrap:wrap;">
                    <div style="text-align:center;">
                        <div style="font-size:3.5rem;font-weight:900;color:var(--accent-cyan);line-height:1;">{{ $product->rating }}</div>
                        <div style="display:flex;gap:2px;justify-content:center;margin:0.4rem 0;">
                            @for($s=1;$s<=5;$s++)
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="{{ $s<=round($product->rating)?'#F59E0B':'none' }}" stroke="#F59E0B" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            @endfor
                        </div>
                        <div style="font-size:0.8rem;color:var(--text-muted);">{{ number_format($product->reviews_count) }} reviews</div>
                    </div>
                    <div style="flex:1;min-width:200px;">
                        @foreach([[5,82],[4,11],[3,5],[2,1],[1,1]] as [$star,$pct])
                        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.4rem;">
                            <span style="font-size:0.78rem;color:var(--text-muted);width:1.5rem;text-align:right;">{{ $star }}&#9733;</span>
                            <div style="flex:1;height:6px;background:rgba(255,255,255,0.07);border-radius:100px;overflow:hidden;">
                                <div style="height:100%;width:{{ $pct }}%;background:linear-gradient(90deg,#F59E0B,#F59E0B90);border-radius:100px;"></div>
                            </div>
                            <span style="font-size:0.75rem;color:var(--text-muted);width:2rem;">{{ $pct }}%</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                <p style="color:var(--text-muted);font-size:0.85rem;padding-top:1rem;border-top:1px solid var(--border-subtle);">Authentication required to post reviews.</p>
            </div>
        </div>

        {{-- Related Products --}}
        @if($related->count() > 0)
        <div style="margin-top:4rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.75rem;">
                <h2 style="font-size:1.4rem;font-weight:800;color:var(--text-primary);">You May Also Like</h2>
                <a href="{{ route('shop.index', ['category' => $product->category]) }}" style="font-size:0.85rem;color:var(--accent-cyan);text-decoration:none;display:flex;align-items:center;gap:0.35rem;">
                    See all in {{ $product->category }}
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="market-grid">
                @foreach($related as $p)
                @include('shop._card', ['p' => $p])
                @endforeach
            </div>
        </div>
        @endif

    </div>
</section>

<style>
.pdp-grid { display:grid; grid-template-columns:1fr 1fr; gap:3.5rem; margin-bottom:3.5rem; }
.pdp-main-img-wrap { position:relative; border-radius:1.25rem; overflow:hidden; background:rgba(11,15,20,0.6); border:1px solid rgba(34,242,226,0.1); cursor:zoom-in; }
.pdp-main-img { width:100%; aspect-ratio:1/1; object-fit:cover; display:block; transition:transform .4s ease; }
.pdp-main-img-wrap:hover .pdp-main-img { transform:scale(1.08); }
.pdp-thumb { width:72px; height:72px; border-radius:0.6rem; overflow:hidden; background:rgba(17,24,39,0.6); border:2px solid transparent; cursor:pointer; padding:0; transition:border-color .2s; flex-shrink:0; }
.pdp-thumb.active, .pdp-thumb:hover { border-color:var(--accent-cyan); }
.pdp-price-block { background:rgba(34,242,226,0.03); border:1px solid rgba(34,242,226,0.08); border-radius:1rem; padding:1.25rem 1.25rem 1rem; }
.pdp-qty { display:flex; align-items:center; gap:0; background:rgba(17,24,39,0.8); border:1px solid var(--border-glow); border-radius:0.625rem; overflow:hidden; }
.pdp-qty input[type=number] { background:transparent !important; color:var(--text-primary) !important; color-scheme:dark; }
.pdp-qty input[type=number]::-webkit-inner-spin-button, .pdp-qty input[type=number]::-webkit-outer-spin-button { -webkit-appearance:none; margin:0; }
.pdp-qty-btn { width:36px; height:42px; background:transparent; border:none; color:var(--text-secondary); font-size:1.1rem; font-weight:700; cursor:pointer; transition:background .2s; }
.pdp-qty-btn:hover { background:rgba(34,242,226,0.08); color:var(--accent-cyan); }
.pdp-cart-btn { padding:0.75rem 1.75rem; background:linear-gradient(135deg,var(--accent-cyan),var(--accent-violet)); border:none; border-radius:0.75rem; color:var(--bg-primary); font-size:0.95rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:0.5rem; transition:all .25s; letter-spacing:0.01em; }
.pdp-cart-btn:hover { filter:brightness(1.1); transform:translateY(-1px); box-shadow:0 8px 24px rgba(34,242,226,0.3); }
.pdp-wish-btn { width:46px; height:46px; border-radius:0.75rem; background:rgba(17,24,39,0.8); border:1px solid var(--border-glow); color:var(--text-muted); display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all .2s; flex-shrink:0; }
.pdp-wish-btn:hover, .pdp-wish-btn.active { border-color:rgba(239,68,68,0.5); color:#f87171; background:rgba(239,68,68,0.08); }
.pdp-wish-btn.active svg { fill:#f87171; stroke:#f87171; }
.pdp-tabs-wrap { background:rgba(17,24,39,0.5); border:1px solid var(--border-subtle); border-radius:1.25rem; overflow:hidden; margin-bottom:3rem; }
.pdp-tabs { display:flex; border-bottom:1px solid var(--border-subtle); padding:0 1.5rem; gap:0; overflow-x:auto; }
.pdp-tab { padding:1rem 1.5rem; background:transparent; border:none; color:var(--text-muted); font-size:0.88rem; font-weight:600; cursor:pointer; border-bottom:2px solid transparent; transition:all .2s; white-space:nowrap; margin-bottom:-1px; }
.pdp-tab.active { color:var(--accent-cyan); border-bottom-color:var(--accent-cyan); }
.pdp-tab:hover:not(.active) { color:var(--text-secondary); }
.pdp-tab-content { display:none; padding:1.75rem; }
.pdp-tab-content.active { display:block; }
@media(max-width:900px) { .pdp-grid { grid-template-columns:1fr !important; gap:2rem !important; } }
</style>
@endsection

@section('extra_scripts')
<script>
function showTab(id, btn) {
    document.querySelectorAll('.pdp-tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.pdp-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('tab-' + id).classList.add('active');
    btn.classList.add('active');
}
</script>
@endsection
