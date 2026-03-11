@extends('layouts.app')

@section('title', 'Stories — Deep Dives & Discoveries | Curevia')
@section('meta_description', 'Read long-form stories and explorations into the most fascinating topics on Curevia.')

@section('content')

{{-- Header --}}
<section style="padding:8rem 0 3rem;position:relative;z-index:1;">
    <div style="max-width:1280px;margin:0 auto;padding:0 1.5rem;">
        <div class="section-header" style="margin-bottom:2rem;">
            <div class="section-label">Stories</div>
            <h1 class="section-title">Deep Dives & Discoveries</h1>
            <p class="section-desc">Long-form explorations into the most fascinating topics on Earth and beyond.</p>
        </div>

        {{-- Search --}}
        <div style="max-width:680px;margin:0 auto 2rem;">
            <form action="{{ route('stories.index') }}" method="GET" style="position:relative;">
                <input type="text" name="q" value="{{ request('q') }}" class="search-bar" placeholder="Search stories..." style="border-radius:1rem;padding:1rem 4rem 1rem 1.5rem;" autocomplete="off">
                <button type="submit" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:linear-gradient(135deg,var(--accent-cyan),var(--accent-violet));border:none;border-radius:100px;padding:0.55rem 1rem;cursor:pointer;color:var(--bg-primary);font-weight:700;font-size:0.8rem;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>
                </button>
            </form>
        </div>
    </div>
</section>

{{-- Featured Story --}}
@if($featured)
<section style="max-width:1280px;margin:0 auto;padding:0 1.5rem 4rem;">
    <a href="{{ route('stories.show', $featured->slug) }}" style="display:grid;grid-template-columns:1.2fr 1fr;gap:3rem;align-items:center;text-decoration:none;background:rgba(17,24,39,0.5);border:1px solid var(--border-subtle);border-radius:1.5rem;overflow:hidden;transition:all .4s ease;" class="featured-story-card" onmouseover="this.style.borderColor='rgba(34,242,226,0.25)';this.style.boxShadow='0 16px 48px rgba(0,0,0,0.4)'" onmouseout="this.style.borderColor='var(--border-subtle)';this.style.boxShadow='none'">
        <div style="overflow:hidden;height:100%;">
            <img src="{{ $featured->featured_image }}" alt="{{ $featured->title }}" style="width:100%;height:100%;min-height:350px;object-fit:cover;transition:transform .6s ease;" loading="lazy" width="900" height="563">
        </div>
        <div style="padding:2.5rem 2.5rem 2.5rem 0;">
            <span class="trending-badge" style="margin-bottom:1rem;display:inline-flex;">Featured Story</span>
            <h2 style="font-size:clamp(1.5rem,3vw,2.25rem);font-weight:800;color:var(--text-primary);line-height:1.2;margin-bottom:1rem;">{{ $featured->title }}</h2>
            <p style="color:var(--text-secondary);font-size:1rem;line-height:1.7;margin-bottom:1.5rem;">{{ $featured->excerpt }}</p>
            <div style="display:flex;align-items:center;gap:1rem;">
                @if($featured->author)
                <img src="{{ $featured->author->contributorProfile->avatar ?? 'https://i.pravatar.cc/80?img=1' }}" alt="{{ $featured->author->name }}" style="width:36px;height:36px;border-radius:50%;border:1.5px solid rgba(34,242,226,0.3);" width="80" height="80">
                <div>
                    <span style="font-size:0.8rem;font-weight:600;color:var(--text-primary);">{{ $featured->author->name }}</span>
                    <span style="font-size:0.75rem;color:var(--text-muted);display:block;">{{ $featured->read_time }} min read · {{ $featured->published_at?->format('M j, Y') }}</span>
                </div>
                @endif
            </div>
        </div>
    </a>
</section>
@endif

{{-- Stories Grid --}}
<section style="max-width:1280px;margin:0 auto;padding:0 1.5rem 5rem;">
    <div id="stories-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(350px,1fr));gap:2rem;">
        @forelse($stories as $story)
        @include('stories._card', ['story' => $story])
        @empty
        <div style="grid-column:1/-1;text-align:center;padding:4rem 0;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="1" style="margin:0 auto 1rem;opacity:0.5;"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            <p style="font-size:1.1rem;color:var(--text-muted);">No stories found.</p>
        </div>
        @endforelse
    </div>

    @if($stories->hasMorePages())
    <div id="load-more-wrap" style="display:flex;justify-content:center;margin-top:3rem;">
        <button id="load-more-btn" data-next="{{ $stories->nextPageUrl() }}" onclick="loadMoreItems(this, 'stories-grid')" class="btn-primary" style="padding:0.85rem 2.5rem;font-size:0.9rem;border-radius:1rem;display:flex;align-items:center;gap:0.6rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="7 13 12 18 17 13"/><polyline points="7 6 12 11 17 6"/></svg>
            Load More Stories
        </button>
    </div>
    @endif
</section>

@endsection

@section('extra_head')
<style>
@media (max-width: 768px) {
    .featured-story-card { grid-template-columns: 1fr !important; }
}
</style>
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
                btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="7 13 12 18 17 13"/><polyline points="7 6 12 11 17 6"/></svg> Load More Stories';
            } else {
                btn.parentElement.innerHTML = '<p style="color:var(--text-muted);font-size:0.9rem;">You\'ve reached the end!</p>';
            }
        }).catch(() => { btn.disabled = false; btn.textContent = 'Retry'; });
}
</script>
@endsection
