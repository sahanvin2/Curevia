<a href="{{ route('encyclopedia.show', $a->slug) }}" class="trending-card reveal" style="opacity:1;transform:none;">
    <div style="overflow:hidden;">
        <img src="{{ $a->featured_image }}" alt="{{ $a->title }}" loading="lazy" width="600" height="400">
    </div>
    <div class="trending-card-body">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.4rem;">
            <div class="trending-badge">{{ $a->category->name }}</div>
            <span style="font-size:0.7rem;color:var(--text-muted);">{{ $a->read_time }} min read</span>
        </div>
        <h3 style="font-size:1.1rem;font-weight:700;color:var(--text-primary);margin-bottom:0.4rem;">{{ $a->title }}</h3>
        <p style="font-size:0.85rem;color:var(--text-secondary);line-height:1.6;">{{ $a->summary }}</p>
    </div>
</a>
