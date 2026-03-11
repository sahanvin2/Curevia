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
            ['label' => 'Dashboard', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>', 'active' => true],
            ['label' => 'Articles', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>', 'active' => false],
            ['label' => 'Stories', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>', 'active' => false],
            ['label' => 'Products', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>', 'active' => false],
            ['label' => 'Users', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>', 'active' => false],
            ['label' => 'Contributors', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>', 'active' => false],
            ['label' => 'Categories', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>', 'active' => false],
            ['label' => 'Analytics', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>', 'active' => false],
            ['label' => 'Settings', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>', 'active' => false],
        ];
        @endphp

        @foreach($sidebarLinks as $link)
        @php
        $linkHref = '#';
        if ($link['label'] === 'Dashboard') $linkHref = route('admin.dashboard');
        if ($link['label'] === 'Products')  $linkHref = route('admin.products.index');
        @endphp
        <a href="{{ $linkHref }}" class="sidebar-link {{ $link['active'] ? 'active' : '' }}">
            {!! $link['icon'] !!}
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
<main style="margin-left:260px;padding:2rem;">

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
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1.25rem;margin-bottom:2.5rem;">
        @foreach([
            ['label' => 'Total Articles', 'value' => '42,847', 'change' => '+124 this week', 'color' => 'var(--accent-cyan)'],
            ['label' => 'Total Users', 'value' => '1.2M', 'change' => '+8,340 this week', 'color' => 'var(--accent-violet)'],
            ['label' => 'Page Views', 'value' => '28.5M', 'change' => '+12% vs last month', 'color' => 'var(--accent-emerald)'],
            ['label' => 'Revenue', 'value' => '$48,290', 'change' => '+18% vs last month', 'color' => 'var(--accent-gold)'],
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
                <a href="#" style="font-size:0.8rem;color:var(--accent-cyan);text-decoration:none;">View All →</a>
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
                    @foreach([
                        ['title' => 'Black Holes: A Complete Guide', 'category' => 'Space', 'views' => '45.2K', 'status' => 'Published'],
                        ['title' => 'The Human Brain', 'category' => 'Human Body', 'views' => '38.1K', 'status' => 'Published'],
                        ['title' => 'Amazon Rainforest', 'category' => 'Nature', 'views' => '29.8K', 'status' => 'Published'],
                        ['title' => 'Quantum Computing 101', 'category' => 'Technology', 'views' => '12.3K', 'status' => 'Draft'],
                        ['title' => 'Norse Mythology Guide', 'category' => 'Mythology', 'views' => '8.7K', 'status' => 'Review'],
                    ] as $row)
                    <tr style="border-bottom:1px solid var(--border-subtle);">
                        <td style="padding:0.85rem 0;font-size:0.85rem;font-weight:600;color:var(--text-primary);">{{ $row['title'] }}</td>
                        <td style="padding:0.85rem 0;"><span class="trending-badge">{{ $row['category'] }}</span></td>
                        <td style="padding:0.85rem 0;font-size:0.85rem;color:var(--text-secondary);">{{ $row['views'] }}</td>
                        <td style="padding:0.85rem 0;">
                            <span style="font-size:0.7rem;font-weight:600;padding:0.2rem 0.6rem;border-radius:100px;
                                {{ $row['status'] === 'Published' ? 'background:rgba(34,242,226,0.1);color:var(--accent-cyan);' : '' }}
                                {{ $row['status'] === 'Draft' ? 'background:rgba(245,158,11,0.1);color:var(--accent-gold);' : '' }}
                                {{ $row['status'] === 'Review' ? 'background:rgba(124,108,255,0.1);color:var(--accent-violet);' : '' }}
                            ">{{ $row['status'] }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Top Contributors --}}
        <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;padding:1.5rem;">
            <h3 style="font-size:1rem;font-weight:700;color:var(--text-primary);margin-bottom:1.25rem;">Top Contributors</h3>
            @foreach([
                ['name' => 'Dr. Elena Vasquez', 'articles' => 342, 'avatar' => 'https://i.pravatar.cc/80?img=32'],
                ['name' => 'Prof. James Okoro', 'articles' => 287, 'avatar' => 'https://i.pravatar.cc/80?img=11'],
                ['name' => 'Dr. Maya Chen', 'articles' => 198, 'avatar' => 'https://i.pravatar.cc/80?img=26'],
                ['name' => 'Dr. Amir Petrov', 'articles' => 165, 'avatar' => 'https://i.pravatar.cc/80?img=53'],
            ] as $c)
            <div style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border-subtle);' : '' }}">
                <img src="{{ $c['avatar'] }}" alt="{{ $c['name'] }}" style="width:36px;height:36px;border-radius:50%;" width="80" height="80" loading="lazy">
                <div style="flex:1;">
                    <div style="font-size:0.85rem;font-weight:600;color:var(--text-primary);">{{ $c['name'] }}</div>
                    <div style="font-size:0.75rem;color:var(--text-muted);">{{ $c['articles'] }} articles</div>
                </div>
                <span class="reputation-badge" style="font-size:0.65rem;padding:0.2rem 0.5rem;">#{{ $loop->iteration }}</span>
            </div>
            @endforeach
        </div>
    </div>

</main>

<style>
@media (max-width: 1024px) {
    #admin-sidebar { transform: translateX(-100%); }
    main { margin-left: 0 !important; }
    main > div:nth-child(3) { grid-template-columns: 1fr !important; }
}
@media (max-width: 768px) {
    main > div:nth-child(2) { grid-template-columns: repeat(2, 1fr) !important; }
}
</style>

</body>
</html>
