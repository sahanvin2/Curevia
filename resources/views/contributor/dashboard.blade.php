@extends('layouts.app')

@section('title', 'Contributor Dashboard | Curevia')

@section('content')
<section style="padding:7rem 0 3rem;position:relative;z-index:1;">
<div style="max-width:1280px;margin:0 auto;padding:0 1.5rem;">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:2.5rem;">
        <div>
            <h1 style="font-size:1.75rem;font-weight:800;color:var(--text-primary);margin-bottom:0.25rem;">Contributor Dashboard</h1>
            <p style="color:var(--text-secondary);">Welcome back, {{ auth()->user()->name }}!</p>
        </div>
        <a href="{{ route('contributor.articles.create') }}" class="btn-primary" style="display:inline-flex;align-items:center;gap:0.5rem;text-decoration:none;padding:0.75rem 1.5rem;font-size:0.875rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Write Article
        </a>
    </div>

    @if(session('success'))
    <div style="background:rgba(34,242,226,0.1);border:1px solid var(--accent-cyan);color:var(--accent-cyan);padding:0.75rem 1rem;border-radius:0.75rem;margin-bottom:1.5rem;font-size:0.875rem;">{{ session('success') }}</div>
    @endif

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1.25rem;margin-bottom:3rem;">
        @foreach([
            ['label'=>'Total Articles', 'value'=>$totalArticles,     'color'=>'var(--accent-cyan)'],
            ['label'=>'Published',      'value'=>$publishedArticles,  'color'=>'var(--accent-emerald)'],
            ['label'=>'Drafts',         'value'=>$draftArticles,      'color'=>'var(--accent-gold)'],
            ['label'=>'Total Views',    'value'=>number_format($totalViews), 'color'=>'var(--accent-violet)'],
            ['label'=>'Reputation',     'value'=>$profile->reputation, 'color'=>'var(--accent-cyan)'],
        ] as $stat)
        <div class="stat-card">
            <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.4rem;">{{ $stat['label'] }}</div>
            <div style="font-size:1.75rem;font-weight:900;color:{{ $stat['color'] }};">{{ $stat['value'] }}</div>
        </div>
        @endforeach
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">

        {{-- Recent Articles --}}
        <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;padding:1.5rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                <h3 style="font-size:1rem;font-weight:700;color:var(--text-primary);">Your Articles</h3>
                <a href="{{ route('contributor.articles') }}" style="font-size:0.8rem;color:var(--accent-cyan);text-decoration:none;">View All →</a>
            </div>
            @forelse($myArticles as $article)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:0.875rem 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border-subtle);' : '' }}">
                <div style="flex:1;min-width:0;">
                    <div style="font-size:0.875rem;font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $article->title }}</div>
                    <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.15rem;">{{ $article->created_at->format('M d, Y') }} · {{ number_format($article->views) }} views</div>
                </div>
                <div style="display:flex;align-items:center;gap:0.5rem;flex-shrink:0;margin-left:1rem;">
                    @php $sc = ['published'=>'background:rgba(34,242,226,0.1);color:var(--accent-cyan);','draft'=>'background:rgba(245,158,11,0.1);color:var(--accent-gold);','review'=>'background:rgba(124,108,255,0.1);color:var(--accent-violet);'][$article->status] ?? ''; @endphp
                    <span style="font-size:0.7rem;font-weight:600;padding:0.2rem 0.6rem;border-radius:100px;{{ $sc }}">{{ ucfirst($article->status) }}</span>
                    <a href="{{ route('contributor.articles.edit', $article) }}" style="padding:0.3rem 0.65rem;font-size:0.75rem;border-radius:0.4rem;background:rgba(34,242,226,0.1);color:var(--accent-cyan);text-decoration:none;font-weight:600;">Edit</a>
                </div>
            </div>
            @empty
            <div style="padding:2rem;text-align:center;">
                <p style="color:var(--text-muted);margin-bottom:1rem;">You haven't written any articles yet.</p>
                <a href="{{ route('contributor.articles.create') }}" class="btn-primary" style="display:inline-block;padding:0.65rem 1.25rem;font-size:0.85rem;">Write Your First Article</a>
            </div>
            @endforelse
        </div>

        {{-- Profile & Tips --}}
        <div style="display:flex;flex-direction:column;gap:1.25rem;">
            <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;padding:1.5rem;">
                <h3 style="font-size:1rem;font-weight:700;color:var(--text-primary);margin-bottom:1.25rem;">Your Profile</h3>
                <div style="text-align:center;margin-bottom:1rem;">
                    <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--accent-cyan),var(--accent-violet));display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:800;color:var(--bg-primary);margin:0 auto 0.75rem;">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
                    <div style="font-size:1rem;font-weight:700;color:var(--text-primary);">{{ auth()->user()->name }}</div>
                    <div style="font-size:0.8rem;color:var(--text-muted);">{{ $profile->expertise ?? 'Contributor' }}</div>
                </div>
                @if($profile->bio)
                <p style="font-size:0.8rem;color:var(--text-secondary);text-align:center;line-height:1.6;">{{ $profile->bio }}</p>
                @endif
            </div>

            <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;padding:1.5rem;">
                <h3 style="font-size:0.875rem;font-weight:700;color:var(--text-primary);margin-bottom:1rem;">Article Status Guide</h3>
                @foreach([
                    ['status'=>'Draft',     'color'=>'var(--accent-gold)',    'desc'=>'Saved but not submitted'],
                    ['status'=>'In Review', 'color'=>'var(--accent-violet)',  'desc'=>'Waiting for admin approval'],
                    ['status'=>'Published', 'color'=>'var(--accent-cyan)',    'desc'=>'Live on the platform'],
                ] as $guide)
                <div style="display:flex;gap:0.75rem;margin-bottom:0.75rem;align-items:flex-start;">
                    <div style="width:8px;height:8px;border-radius:50%;background:{{ $guide['color'] }};flex-shrink:0;margin-top:0.3rem;"></div>
                    <div>
                        <div style="font-size:0.8rem;font-weight:600;color:{{ $guide['color'] }};">{{ $guide['status'] }}</div>
                        <div style="font-size:0.75rem;color:var(--text-muted);">{{ $guide['desc'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

</div>
</section>

<style>
@media (max-width: 900px) {
    section > div > div:last-child { grid-template-columns: 1fr !important; }
}
</style>
@endsection
