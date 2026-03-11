<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Analytics | Curevia Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background:#0B0F14;color:#F0F4FF;font-family:'Inter',system-ui,sans-serif;">

@include('admin.layouts.sidebar')

<main style="margin-left:260px;padding:2rem;" id="admin-main">

    <button id="admin-sidebar-toggle" onclick="document.getElementById('admin-sidebar').classList.toggle('open')" style="display:none;position:fixed;top:1rem;left:1rem;z-index:1001;width:40px;height:40px;border-radius:10px;background:rgba(17,24,39,0.9);border:1px solid var(--border-glow);color:var(--accent-cyan);cursor:pointer;align-items:center;justify-content:center;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>

    <div style="margin-bottom:2rem;">
        <h1 style="font-size:1.5rem;font-weight:800;">Analytics</h1>
        <p style="font-size:0.85rem;color:var(--text-muted);">Platform-wide statistics &amp; insights.</p>
    </div>

    {{-- Top stats --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1.25rem;margin-bottom:2.5rem;">
        <div class="stat-card">
            <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.5rem;">Total Page Views</div>
            <div style="font-size:2rem;font-weight:900;color:var(--accent-cyan);">{{ number_format($totalViews) }}</div>
        </div>
        @foreach($usersByRole as $roleRow)
        <div class="stat-card">
            <div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.5rem;">{{ ucfirst($roleRow->role) }}s</div>
            <div style="font-size:2rem;font-weight:900;color:var(--accent-violet);">{{ number_format($roleRow->count) }}</div>
        </div>
        @endforeach
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">

        {{-- Top Articles --}}
        <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;padding:1.5rem;">
            <h3 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;">Top Articles by Views</h3>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border-subtle);">
                        <th style="text-align:left;padding:0.5rem 0;font-size:0.7rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;">#</th>
                        <th style="text-align:left;padding:0.5rem 0;font-size:0.7rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;">Article</th>
                        <th style="text-align:right;padding:0.5rem 0;font-size:0.7rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;">Views</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topArticles as $i => $article)
                    <tr style="border-bottom:1px solid var(--border-subtle);">
                        <td style="padding:0.625rem 0;font-size:0.75rem;color:var(--text-muted);width:30px;">{{ $i + 1 }}</td>
                        <td style="padding:0.625rem 0.5rem;">
                            <div style="font-size:0.8rem;font-weight:600;color:var(--text-primary);">{{ Str::limit($article->title, 38) }}</div>
                            <div style="font-size:0.7rem;color:var(--text-muted);">{{ $article->category->name ?? '—' }}</div>
                        </td>
                        <td style="padding:0.625rem 0;text-align:right;font-size:0.875rem;font-weight:700;color:var(--accent-cyan);">{{ number_format($article->views) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="padding:2rem 0;text-align:center;color:var(--text-muted);font-size:0.85rem;">No data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Articles by Category --}}
        <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;padding:1.5rem;">
            <h3 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;">Articles by Category</h3>
            @forelse($articlesByCategory as $cat)
            @php $pct = $articlesByCategory->sum('articles_count') > 0 ? round(($cat->articles_count / $articlesByCategory->sum('articles_count')) * 100) : 0; @endphp
            <div style="margin-bottom:0.875rem;">
                <div style="display:flex;justify-content:space-between;margin-bottom:0.3rem;">
                    <span style="font-size:0.8rem;font-weight:600;color:var(--text-primary);">{{ $cat->name }}</span>
                    <span style="font-size:0.8rem;color:var(--text-muted);">{{ $cat->articles_count }}</span>
                </div>
                <div style="height:6px;background:rgba(255,255,255,0.06);border-radius:100px;overflow:hidden;">
                    <div style="height:100%;width:{{ $pct }}%;background:linear-gradient(90deg,var(--accent-cyan),var(--accent-violet));border-radius:100px;transition:width 0.5s ease;"></div>
                </div>
            </div>
            @empty
            <p style="font-size:0.85rem;color:var(--text-muted);text-align:center;padding:2rem 0;">No data.</p>
            @endforelse
        </div>

    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

        {{-- Top Stories --}}
        <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;padding:1.5rem;">
            <h3 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;">Top Stories by Views</h3>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border-subtle);">
                        <th style="text-align:left;padding:0.5rem 0;font-size:0.7rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;">#</th>
                        <th style="text-align:left;padding:0.5rem 0;font-size:0.7rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;">Story</th>
                        <th style="text-align:right;padding:0.5rem 0;font-size:0.7rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;">Views</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topStories as $i => $story)
                    <tr style="border-bottom:1px solid var(--border-subtle);">
                        <td style="padding:0.625rem 0;font-size:0.75rem;color:var(--text-muted);width:30px;">{{ $i + 1 }}</td>
                        <td style="padding:0.625rem 0.5rem;font-size:0.8rem;font-weight:600;color:var(--text-primary);">{{ Str::limit($story->title, 38) }}</td>
                        <td style="padding:0.625rem 0;text-align:right;font-size:0.875rem;font-weight:700;color:var(--accent-emerald);">{{ number_format($story->views) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="padding:2rem 0;text-align:center;color:var(--text-muted);font-size:0.85rem;">No data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Recent Registrations --}}
        <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;padding:1.5rem;">
            <h3 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;">Recent Registrations</h3>
            @forelse($recentUsers as $ru)
            <div style="display:flex;align-items:center;gap:0.75rem;padding:0.6rem 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border-subtle);' : '' }}">
                <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--accent-violet),var(--accent-cyan));display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:800;color:var(--bg-primary);flex-shrink:0;">{{ strtoupper(substr($ru->name,0,1)) }}</div>
                <div style="flex:1;">
                    <div style="font-size:0.8rem;font-weight:600;color:var(--text-primary);">{{ $ru->name }}</div>
                    <div style="font-size:0.7rem;color:var(--text-muted);">{{ $ru->email }}</div>
                </div>
                <span style="font-size:0.7rem;color:var(--text-muted);">{{ $ru->created_at->diffForHumans() }}</span>
            </div>
            @empty
            <p style="font-size:0.85rem;color:var(--text-muted);text-align:center;padding:2rem 0;">No users.</p>
            @endforelse
        </div>

    </div>

</main>

<style>
@media (max-width: 1024px) {
    #admin-sidebar { transform: translateX(-100%); position: fixed; top:0; left:0; bottom:0; z-index:1000; transition: transform 0.3s ease; }
    #admin-sidebar.open { transform: translateX(0); }
    #admin-main { margin-left: 0 !important; }
    #admin-sidebar-toggle { display: flex !important; }
    #admin-main > div:not(:first-child):not(:nth-child(2)) { grid-template-columns: 1fr !important; }
}
@media (max-width: 640px) {
    #admin-main { padding: 1rem !important; padding-top: 3.5rem !important; }
}
</style>
</body>
</html>
