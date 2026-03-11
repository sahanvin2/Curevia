@extends('layouts.app')

@section('title', 'Knowledge Marketplace — Shop | Curevia')
@section('meta_description', 'Discover curated products for curious minds — telescopes, books, educational kits, and more on the Curevia marketplace.')

@section('content')

{{-- Shop Hero Banner --}}
<section class="shop-hero-banner">
    <div style="max-width:1280px;margin:0 auto;padding:0 1.5rem;">
        <div class="shop-hero-inner">
            <div>
                <div style="display:inline-flex;align-items:center;gap:0.5rem;background:rgba(34,242,226,0.08);border:1px solid rgba(34,242,226,0.2);border-radius:100px;padding:0.35rem 1rem;margin-bottom:1rem;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--accent-cyan)" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span style="font-size:0.72rem;font-weight:700;color:var(--accent-cyan);letter-spacing:0.08em;text-transform:uppercase;">Expert Curated</span>
                </div>
                <h1 style="font-size:clamp(1.8rem,4vw,2.75rem);font-weight:900;color:var(--text-primary);line-height:1.2;margin-bottom:0.75rem;">Tools for <span class="gradient-text">Curious Minds</span></h1>
                <p style="color:var(--text-muted);font-size:0.95rem;max-width:460px;line-height:1.7;">Books, telescopes, educational tools, and more — handpicked to fuel your passion for discovery.</p>

                {{-- Search --}}
                <form action="{{ route('shop.index') }}" method="GET" style="position:relative;max-width:440px;margin-top:1.5rem;">
                    @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    <input type="text" name="q" value="{{ request('q') }}" class="search-bar" placeholder="Search products, books, tools..." style="padding:0.9rem 3.5rem 0.9rem 1.25rem;border-radius:0.75rem;" autocomplete="off">
                    <button type="submit" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:linear-gradient(135deg,var(--accent-cyan),var(--accent-violet));border:none;border-radius:0.5rem;width:36px;height:36px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--bg-primary);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>
                    </button>
                </form>
            </div>

            {{-- Trust badges horizontal --}}
            <div class="shop-trust-strip">
                <div class="shop-trust-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-cyan)" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <div><div style="font-size:0.8rem;font-weight:700;color:var(--text-primary);">Secure</div><div style="font-size:0.7rem;color:var(--text-muted);">SSL Encrypted</div></div>
                </div>
                <div class="shop-trust-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-violet)" stroke-width="1.5"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    <div><div style="font-size:0.8rem;font-weight:700;color:var(--text-primary);">Free Ship</div><div style="font-size:0.7rem;color:var(--text-muted);">Orders $50+</div></div>
                </div>
                <div class="shop-trust-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="1.5"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                    <div><div style="font-size:0.8rem;font-weight:700;color:var(--text-primary);">30-Day</div><div style="font-size:0.7rem;color:var(--text-muted);">Easy Returns</div></div>
                </div>
                <div class="shop-trust-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <div><div style="font-size:0.8rem;font-weight:700;color:var(--text-primary);">Curated</div><div style="font-size:0.7rem;color:var(--text-muted);">Expert Picks</div></div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Shop Layout: Sidebar + Products --}}
<section style="max-width:1280px;margin:0 auto;padding:1.5rem 1.5rem 5rem;">
    <div class="shop-layout">

        {{-- Sidebar --}}
        <aside class="shop-sidebar">
            <div class="shop-sidebar-card">
                <h3 class="shop-sidebar-heading">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    Categories
                </h3>
                <div class="shop-cat-list">
                    <a href="{{ route('shop.index', array_filter(['q' => request('q')])) }}" class="shop-cat-item {{ !request('category') ? 'active' : '' }}">
                        <span>All Products</span>
                        <span class="shop-cat-count">{{ $products->total() }}</span>
                    </a>
                    @foreach($productCategories as $cat)
                    <a href="{{ route('shop.index', array_filter(['category' => $cat, 'q' => request('q')])) }}" class="shop-cat-item {{ request('category') === $cat ? 'active' : '' }}">
                        <span>{{ $cat }}</span>
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Price Range --}}
            <div class="shop-sidebar-card" style="margin-top:1rem;">
                <h3 class="shop-sidebar-heading">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    Price Range
                </h3>
                <div style="display:flex;flex-direction:column;gap:0.5rem;">
                    @foreach([['Under $20','0','20'],['$20 – $50','20','50'],['$50 – $100','50','100'],['Over $100','100','']] as [$label,$min,$max])
                    <a href="{{ route('shop.index', array_filter(['category' => request('category'), 'q' => request('q'), 'min' => $min, 'max' => $max])) }}" class="shop-price-filter {{ request('min') === $min ? 'active' : '' }}" style="font-size:0.82rem;color:{{ request('min') === $min ? 'var(--accent-cyan)' : 'var(--text-muted)' }};text-decoration:none;padding:0.35rem 0;display:flex;align-items:center;gap:0.5rem;transition:color .2s;" onmouseover="this.style.color='var(--accent-cyan)'" onmouseout="this.style.color='{{ request('min') === $min ? 'var(--accent-cyan)' : 'var(--text-muted)' }}'">
                        <span style="width:8px;height:8px;border-radius:50%;background:{{ request('min') === $min ? 'var(--accent-cyan)' : 'var(--border-glow)' }};"></span>
                        {{ $label }}
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Rating Filter --}}
            <div class="shop-sidebar-card" style="margin-top:1rem;">
                <h3 class="shop-sidebar-heading">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="#F59E0B" stroke="#F59E0B" stroke-width="1"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    Rating
                </h3>
                @foreach([[4,'4★ & up'],[3,'3★ & up'],[2,'2★ & up']] as [$r,$label])
                <a href="{{ route('shop.index', array_filter(['category' => request('category'), 'q' => request('q'), 'rating' => $r])) }}" style="display:flex;align-items:center;gap:0.5rem;padding:0.35rem 0;text-decoration:none;color:{{ request('rating') == $r ? 'var(--accent-cyan)' : 'var(--text-muted)' }};font-size:0.82rem;transition:color .2s;" onmouseover="this.style.color='var(--accent-cyan)'" onmouseout="this.style.color='{{ request('rating') == $r ? 'var(--accent-cyan)' : 'var(--text-muted)' }}'">
                    <div style="display:flex;gap:1px;">@for($i=1;$i<=5;$i++)<svg width="11" height="11" viewBox="0 0 24 24" fill="{{ $i<=$r ? '#F59E0B' : 'none' }}" stroke="#F59E0B" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>@endfor</div>
                    <span>{{ $label }}</span>
                </a>
                @endforeach
            </div>
        </aside>

        {{-- Main Products Area --}}
        <div class="shop-main">

            {{-- Toolbar --}}
            <div class="shop-toolbar">
                <div style="font-size:0.85rem;color:var(--text-muted);">
                    @if(request('q') || request('category'))
                    Results for
                    @if(request('q'))<strong style="color:var(--text-primary);">"{{ request('q') }}"</strong>@endif
                    @if(request('category'))<span style="color:var(--accent-violet);font-weight:600;">{{ request('category') }}</span>@endif
                    —
                    @endif
                    <strong style="color:var(--text-primary);">{{ $products->total() }}</strong> products
                </div>
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <span style="font-size:0.8rem;color:var(--text-muted);">Sort:</span>
                    <select onchange="location='?'+new URLSearchParams({...Object.fromEntries(new URLSearchParams(location.search)),...{sort:this.value}}).toString()" style="background:rgba(17,24,39,0.8);border:1px solid var(--border-glow);border-radius:0.5rem;color:var(--text-secondary);padding:0.35rem 0.75rem;font-size:0.8rem;cursor:pointer;outline:none;">
                        <option value="featured" {{ request('sort','featured')=='featured'?'selected':'' }}>Featured</option>
                        <option value="price_asc" {{ request('sort')=='price_asc'?'selected':'' }}>Price: Low → High</option>
                        <option value="price_desc" {{ request('sort')=='price_desc'?'selected':'' }}>Price: High → Low</option>
                        <option value="rating" {{ request('sort')=='rating'?'selected':'' }}>Top Rated</option>
                    </select>
                </div>
            </div>

            {{-- Products Grid --}}
            <div id="products-grid" class="market-grid">
                @forelse($products as $p)
                @include('shop._card', ['p' => $p])
                @empty
                <div style="grid-column:1/-1;text-align:center;padding:5rem 0;">
                    <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="1" style="margin:0 auto 1.25rem;display:block;opacity:0.4;"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    <p style="font-size:1.1rem;color:var(--text-muted);font-weight:500;">No products found</p>
                    <a href="{{ route('shop.index') }}" class="btn-secondary" style="display:inline-flex;margin-top:1rem;padding:0.6rem 1.5rem;font-size:0.85rem;">Browse All Products</a>
                </div>
                @endforelse
            </div>

            {{-- Load More --}}
            @if($products->hasMorePages())
            <div id="load-more-wrap" style="display:flex;justify-content:center;margin-top:2.5rem;">
                <button id="load-more-btn" data-next="{{ $products->nextPageUrl() }}" onclick="loadMoreItems(this, 'products-grid')" class="btn-secondary" style="padding:0.75rem 2.5rem;font-size:0.88rem;display:flex;align-items:center;gap:0.6rem;border-radius:0.75rem;">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="7 13 12 18 17 13"/><polyline points="7 6 12 11 17 6"/></svg>
                    Load More
                </button>
            </div>
            @endif
        </div>
    </div>
</section>

@endsection

@section('extra_scripts')
<script>
function loadMoreItems(btn, gridId) {
    const url = btn.dataset.next;
    if (!url) return;
    btn.disabled = true;
    btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin 1s linear infinite;"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Loading...';
    fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
        .then(r => r.json())
        .then(data => {
            document.getElementById(gridId).insertAdjacentHTML('beforeend', data.html);
            if (data.next) {
                btn.dataset.next = data.next;
                btn.disabled = false;
                btn.innerHTML = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="7 13 12 18 17 13"/><polyline points="7 6 12 11 17 6"/></svg> Load More';
            } else {
                btn.closest('#load-more-wrap').innerHTML = '<p style="color:var(--text-muted);font-size:0.85rem;text-align:center;">You\'ve seen all products!</p>';
            }
        }).catch(() => { btn.disabled = false; btn.textContent = 'Retry'; });
}
function addToCartFeedback(btn) {
    const orig = btn.innerHTML;
    btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6L9 17l-5-5"/></svg> Added!';
    btn.style.background = 'linear-gradient(135deg,#34D399,#059669)';
    setTimeout(() => { btn.innerHTML = orig; btn.style.background = ''; }, 1800);
}
</script>
@endsection
