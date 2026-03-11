<a href="{{ route('stories.show', $story->slug) }}" class="glass-card reveal" style="text-decoration:none;overflow:hidden;opacity:1;transform:none;">
    <div style="overflow:hidden;height:220px;">
        <img src="{{ $story->featured_image }}" alt="{{ $story->title }}" style="width:100%;height:100%;object-fit:cover;transition:transform .5s ease;" loading="lazy" width="700" height="438">
    </div>
    <div style="padding:1.5rem;">
        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;">
            <span class="trending-badge">{{ $story->category->name }}</span>
            <span style="font-size:0.75rem;color:var(--text-muted);">{{ $story->read_time }} min read</span>
        </div>
        <h3 style="font-size:1.15rem;font-weight:700;color:var(--text-primary);line-height:1.3;margin-bottom:0.6rem;">{{ $story->title }}</h3>
        <p style="font-size:0.85rem;color:var(--text-secondary);line-height:1.6;margin-bottom:1rem;">{{ $story->excerpt }}</p>
        <div style="display:flex;align-items:center;gap:0.75rem;">
            @if($story->author)
            <img src="{{ $story->author->contributorProfile->avatar ?? 'https://i.pravatar.cc/80?img=1' }}" alt="{{ $story->author->name }}" style="width:28px;height:28px;border-radius:50%;" width="80" height="80" loading="lazy">
            <div>
                <span style="font-size:0.75rem;font-weight:600;color:var(--text-primary);">{{ $story->author->name }}</span>
                <span style="font-size:0.7rem;color:var(--text-muted);display:block;">{{ $story->published_at?->format('M j, Y') }}</span>
            </div>
            @endif
        </div>
    </div>
</a>
