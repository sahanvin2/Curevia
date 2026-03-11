<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard | Curevia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background:#0B0F14;color:#F0F4FF;font-family:'Inter',system-ui,sans-serif;">

{{-- Sidebar --}}
<aside class="sidebar" id="admin-sidebar">
    <div style="padding:1.5rem;">
        <a href="{{ route('home') }}" class="logo-text" style="font-size:1.25rem;text-decoration:none;display:block;margin-bottom:0.5rem;">Curevia</a>
        <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;font-weight:700;">Admin Panel</span>
    </div>

    <nav style="padding:0 0.75rem;margin-top:1rem;">
        @php
        $sidebarLinks = [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard'],
            ['label' => 'Articles',  'route' => 'admin.articles.index'],
            ['label' => 'Products',  'route' => 'admin.products.index'],
            ['label' => 'Users',     'route' => 'admin.users.index'],
            ['label' => 'Analytics', 'route' => 'admin.analytics'],
        ];
        $icons = [
            'Dashboard' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>',
            'Articles'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
            'Products'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>',
            'Users'     => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
            'Analytics' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>',
        ];
        @endphp

        @foreach($sidebarLinks as $link)
        <a href="{{ route($link['route']) }}" class="sidebar-link {{ Route::currentRouteName() === 'admin.dashboard' && $link['label'] === 'Dashboard' ? 'active' : '' }}">
            {!! $icons[$link['label']] !!}
            {{ $link['label'] }}
        </a>
        @endforeach
    </nav>

    <div style="position:absolute;bottom:1.5rem;left:0;right:0;padding:0 0.75rem;">
        <a href="{{ route('home') }}" class="sidebar-link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Back to Site
        </a>
    </div>
</aside>

{{-- Main Content --}}
<main style="margin-left:260px;padding:2rem;" id="admin-main">

    {{-- Mobile sidebar toggle --}}
    <button id="admin-sidebar-toggle" onclick="document.getElementById('admin-sidebar').classList.toggle('open');this.classList.toggle('active')" style="display:none;position:fixed;top:1rem;left:1rem;z-index:1001;width:40px;height:40px;border-radius:10px;background:rgba(17,24,39,0.9);border:1px solid var(--border-glow);color:var(--accent-cyan);cursor:pointer;align-items:center;justify-content:center;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>

    {{-- Top Bar --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2.5rem;">
        <div>
            <h1 style="font-size:1.5rem;font-weight:800;color:var(--text-primary);">Dashboard</h1>
            <p style="font-size:0.85rem;color:var(--text-muted);">Overview of your platform.</p>
        </div>
        <div style="display:flex;align-items:center;gap:1rem;">
            <span style="font-size:0.8rem;color:var(--text-muted);">{{ date('M d, Y') }}</span>
            <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--accent-cyan),var(--accent-violet));display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;color:var(--bg-primary);">A</div>
        </div>
    </div>

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1.25rem;margin-bottom:2.5rem;" class="admin-stats-grid">
        @foreach([
            ['label' => 'Total Articles', 'value' => number_format($totalArticles), 'change' => '+' . $articlesThisWeek . ' this week', 'color' => 'var(--accent-cyan)'],
            ['label' => 'Total Users', 'value' => number_format($totalUsers), 'change' => '+' . $usersThisWeek . ' this week', 'color' => 'var(--accent-violet)'],
            ['label' => 'Page Views', 'value' => number_format($totalPageViews), 'change' => 'Combined article & story views', 'color' => 'var(--accent-emerald)'],
            ['label' => 'Products', 'value' => number_format($totalProducts), 'change' => 'Active in shop', 'color' => 'var(--accent-gold)'],
        ] as $stat)
        <div class="stat-card">
            <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.5rem;">{{ $stat['label'] }}</div>
            <div style="font-size:1.75rem;font-weight:900;color:var(--text-primary);margin-bottom:0.25rem;">{{ $stat['value'] }}</div>
            <div style="font-size:0.75rem;color:{{ $stat['color'] }};">{{ $stat['change'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Content Grid --}}
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">

        {{-- Recent Articles --}}
        <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;padding:1.5rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                <h3 style="font-size:1rem;font-weight:700;color:var(--text-primary);">Recent Articles</h3>
                <a href="{{ route('admin.articles.index') }}" style="font-size:0.8rem;color:var(--accent-cyan);text-decoration:none;">View All →</a>
            </div>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border-subtle);">
                        <th style="text-align:left;padding:0.75rem 0;font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">Title</th>
                        <th style="text-align:left;padding:0.75rem 0;font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">Category</th>
                        <th style="text-align:left;padding:0.75rem 0;font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">Views</th>
                        <th style="text-align:left;padding:0.75rem 0;font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentArticles as $article)
                    <tr style="border-bottom:1px solid var(--border-subtle);">
                        <td style="padding:0.85rem 0;font-size:0.85rem;font-weight:600;color:var(--text-primary);">{{ Str::limit($article->title, 40) }}</td>
                        <td style="padding:0.85rem 0;"><span class="trending-badge">{{ $article->category->name ?? '—' }}</span></td>
                        <td style="padding:0.85rem 0;font-size:0.85rem;color:var(--text-secondary);">{{ number_format($article->views) }}</td>
                        <td style="padding:0.85rem 0;">
                            <span style="font-size:0.7rem;font-weight:600;padding:0.2rem 0.6rem;border-radius:100px;
                                {{ $article->status === 'published' ? 'background:rgba(34,242,226,0.1);color:var(--accent-cyan);' : '' }}
                                {{ $article->status === 'draft' ? 'background:rgba(245,158,11,0.1);color:var(--accent-gold);' : '' }}
                                {{ $article->status === 'review' ? 'background:rgba(124,108,255,0.1);color:var(--accent-violet);' : '' }}
                            ">{{ ucfirst($article->status) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="padding:2rem 0;text-align:center;color:var(--text-muted);font-size:0.85rem;">No articles yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Top Contributors --}}
        <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;padding:1.5rem;">
            <h3 style="font-size:1rem;font-weight:700;color:var(--text-primary);margin-bottom:1.25rem;">Top Contributors</h3>
            @forelse($topContributors as $c)
            <div style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border-subtle);' : '' }}">
                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--accent-cyan),var(--accent-violet));display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:800;color:var(--bg-primary);flex-shrink:0;">{{ strtoupper(substr($c->user->name ?? '?', 0, 1)) }}</div>
                <div style="flex:1;">
                    <div style="font-size:0.85rem;font-weight:600;color:var(--text-primary);">{{ $c->user->name ?? 'Unknown' }}</div>
                    <div style="font-size:0.75rem;color:var(--text-muted);">{{ $c->expertise ?? 'Contributor' }} · Rep: {{ $c->reputation }}</div>
                </div>
                <span class="reputation-badge" style="font-size:0.65rem;padding:0.2rem 0.5rem;">#{{ $loop->iteration }}</span>
            </div>
            @empty
            <p style="font-size:0.85rem;color:var(--text-muted);text-align:center;padding:2rem 0;">No contributors yet.</p>
            @endforelse
        </div>
    </div>

</main>

<style>
@media (max-width: 1024px) {
    #admin-sidebar { transform: translateX(-100%); position: fixed; top: 0; left: 0; bottom: 0; z-index: 1000; transition: transform 0.3s ease; }
    #admin-sidebar.open { transform: translateX(0); }
    #admin-main { margin-left: 0 !important; }
    #admin-sidebar-toggle { display: flex !important; }
    .admin-stats-grid { grid-template-columns: repeat(2, 1fr) !important; }
    main > div:last-of-type { grid-template-columns: 1fr !important; }
}
@media (max-width: 640px) {
    #admin-main { padding: 1rem !important; padding-top: 3.5rem !important; }
    .admin-stats-grid { grid-template-columns: 1fr !important; }
}
</style>

</body>
</html>
