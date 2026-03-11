<div class="market-card reveal">
    {{-- Image --}}
    <div class="market-card-img">
        <a href="{{ route('shop.show', $p->slug) }}" style="display:block;height:100%;">
            <img src="{{ $p->image }}" alt="{{ $p->name }}" loading="lazy" width="400" height="400">
        </a>
        @if($p->badge)
        <span class="mc-badge">{{ $p->badge }}</span>
        @endif
        @if($p->original_price && $p->original_price > $p->price)
        <span class="mc-discount">-{{ round((($p->original_price - $p->price) / $p->original_price) * 100) }}%</span>
        @endif
        <button class="mc-wish" onclick="event.preventDefault();event.stopPropagation();this.classList.toggle('active');" aria-label="Wishlist">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </button>
    </div>

    {{-- Info --}}
    <div class="market-card-body">
        <div class="mc-cat">{{ $p->category }}</div>
        <a href="{{ route('shop.show', $p->slug) }}" class="mc-title-link">
            <h4 class="mc-title">{{ $p->name }}</h4>
        </a>

        {{-- Stars --}}
        <div class="mc-stars">
            @for($s = 1; $s <= 5; $s++)
            <svg width="12" height="12" viewBox="0 0 24 24" fill="{{ $s <= round($p->rating) ? '#F59E0B' : 'none' }}" stroke="#F59E0B" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            @endfor
            <span class="mc-rating">{{ $p->rating }}</span>
            <span class="mc-reviews">({{ number_format($p->reviews_count) }})</span>
        </div>

        {{-- Price --}}
        <div class="mc-price-row">
            <span class="mc-price">${{ number_format($p->price, 2) }}</span>
            @if($p->original_price && $p->original_price > $p->price)
            <span class="mc-price-orig">${{ number_format($p->original_price, 2) }}</span>
            @endif
        </div>

        {{-- Free shipping --}}
        <div class="mc-ship">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            Free Shipping
        </div>

        {{-- Buy Now --}}
        @php
            $cMktName  = 'View Product';
            $cMktColor = 'var(--accent-cyan)';
            if ($p->affiliate_url) {
                if (str_contains($p->affiliate_url, 'amazon'))     { $cMktName = 'Buy on Amazon';     $cMktColor = '#FF9900'; }
                elseif (str_contains($p->affiliate_url, 'aliexpress')) { $cMktName = 'Buy on AliExpress'; $cMktColor = '#FF4747'; }
                elseif (str_contains($p->affiliate_url, 'ebay'))   { $cMktName = 'Buy on eBay';       $cMktColor = '#0064D3'; }
                else { $cMktName = 'Buy Now'; }
            }
            $cBuyHref   = $p->affiliate_url ?: route('shop.show', $p->slug);
            $cBuyTarget = $p->affiliate_url ? '_blank' : '_self';
            $cBuyRel    = $p->affiliate_url ? 'noopener noreferrer' : '';
        @endphp
        <a href="{{ $cBuyHref }}" target="{{ $cBuyTarget }}" rel="{{ $cBuyRel }}"
            class="mc-cart-btn" style="color:{{ $cMktColor }};border-color:{{ $cMktColor }}40;background-color:{{ $cMktColor }}0d;text-decoration:none;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            {{ $cMktName }}
        </a>
    </div>
</div>
