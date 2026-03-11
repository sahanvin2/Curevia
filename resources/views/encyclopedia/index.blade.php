@extends('layouts.app')

@section('title', 'Encyclopedia' . (request('category') ? ' — ' . ucfirst(str_replace('-', ' ', request('category'))) : '') . ' | Curevia')
@section('meta_description', 'Browse the Curevia encyclopedia. Explore thousands of articles on space, science, history, animals, geography, mythology and more.')

@section('content')

{{-- Page Header --}}
<section style="padding:8rem 0 3rem;position:relative;z-index:1;">
    <div style="max-width:1280px;margin:0 auto;padding:0 1.5rem;">
        <div class="section-header" style="margin-bottom:2rem;">
            <div class="section-label">Encyclopedia</div>
            <h1 class="section-title">
                @if(request('q'))
                    Results for "{{ e(request('q')) }}"
                @elseif(request('category'))
                    {{ ucfirst(str_replace('-', ' ', request('category'))) }}
                @else
                    Explore All Knowledge
                @endif
            </h1>
            <p class="section-desc">Thousands of meticulously crafted articles spanning every domain of human knowledge.</p>
        </div>

        {{-- Search --}}
        <div style="max-width:680px;margin:0 auto 3rem;">
            <form action="{{ route('encyclopedia.index') }}" method="GET" style="position:relative;">
                <input type="text" name="q" value="{{ request('q') }}" class="search-bar" placeholder="Search articles..." style="border-radius:1rem;padding:1rem 4rem 1rem 1.5rem;" autocomplete="off">
                <button type="submit" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:linear-gradient(135deg,var(--accent-cyan),var(--accent-violet));border:none;border-radius:100px;padding:0.55rem 1rem;cursor:pointer;color:var(--bg-primary);font-weight:700;font-size:0.8rem;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>
                </button>
            </form>
        </div>

        {{-- Category Filters --}}
        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;justify-content:center;margin-bottom:3rem;">
            <a href="{{ route('encyclopedia.index') }}" class="search-tag {{ !request('category') ? 'active' : '' }}" style="{{ !request('category') ? 'background:rgba(34,242,226,0.15);border-color:var(--accent-cyan);color:var(--accent-cyan);' : '' }}">All</a>
            @foreach($categories as $cat)
            <a href="{{ route('encyclopedia.index', ['category' => $cat->slug]) }}" class="search-tag {{ request('category') === $cat->slug ? 'active' : '' }}" style="{{ request('category') === $cat->slug ? 'background:rgba(34,242,226,0.15);border-color:var(--accent-cyan);color:var(--accent-cyan);' : '' }}">
                {{ $cat->name }}
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- Articles Grid --}}
<section style="max-width:1280px;margin:0 auto;padding:0 1.5rem 5rem;">
    <div id="articles-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.5rem;">
        @forelse($articles as $a)
        @include('encyclopedia._card', ['a' => $a])
        @empty
        <div style="grid-column:1/-1;text-align:center;padding:4rem 0;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="1" style="margin:0 auto 1rem;opacity:0.5;"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <p style="font-size:1.1rem;color:var(--text-muted);">No articles found. Try a different search or category.</p>
        </div>
        @endforelse
    </div>

    @if($articles->hasMorePages())
    <div id="load-more-wrap" style="display:flex;justify-content:center;margin-top:3rem;">
        <button id="load-more-btn" data-next="{{ $articles->nextPageUrl() }}" onclick="loadMoreItems(this, 'articles-grid')" class="btn-primary" style="padding:0.85rem 2.5rem;font-size:0.9rem;border-radius:1rem;display:flex;align-items:center;gap:0.6rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="7 13 12 18 17 13"/><polyline points="7 6 12 11 17 6"/></svg>
            Load More Articles
        </button>
    </div>
    @endif
</section>

{{-- Sidebar Ad --}}
<div style="max-width:1280px;margin:0 auto 3rem;padding:0 1.5rem;">
    <div class="ad-banner" style="padding:1.5rem;">
        <span style="opacity:0.5;">AD</span> — Expand your knowledge with curated learning resources — <a href="#" style="color:var(--accent-cyan);text-decoration:none;">Learn More</a>
    </div>
</div>

@endsection

@section('extra_scripts')
<script>
function loadMoreItems(btn, gridId) {
    const url = btn.dataset.next;
    if (!url) return;
    btn.disabled = true;
    btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin 1s linear infinite;"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Loading...';
    fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
        .then(r => r.json())
        .then(data => {
            document.getElementById(gridId).insertAdjacentHTML('beforeend', data.html);
            if (data.next) {
                btn.dataset.next = data.next;
                btn.disabled = false;
                btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="7 13 12 18 17 13"/><polyline points="7 6 12 11 17 6"/></svg> Load More';
            } else {
                btn.parentElement.innerHTML = '<p style="color:var(--text-muted);font-size:0.9rem;">You\'ve reached the end!</p>';
            }
        }).catch(() => { btn.disabled = false; btn.textContent = 'Retry'; });
}
</script>
@endsection
