{{-- Admin Sidebar partial --}}
<aside class="sidebar" id="admin-sidebar">
    <div style="padding:1.5rem;">
        <a href="{{ route('home') }}" class="logo-text" style="font-size:1.25rem;text-decoration:none;display:block;margin-bottom:0.5rem;">Curevia</a>
        <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;font-weight:700;">Admin Panel</span>
    </div>

    <nav style="padding:0 0.75rem;margin-top:1rem;">
        @php
        $navItems = [
            ['label'=>'Dashboard',    'route'=>'admin.dashboard',        'icon'=>'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>'],
            ['label'=>'Articles',     'route'=>'admin.articles.index',   'icon'=>'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>'],
            ['label'=>'Products',     'route'=>'admin.products.index',   'icon'=>'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>'],
            ['label'=>'Users',        'route'=>'admin.users.index',      'icon'=>'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>'],
            ['label'=>'Analytics',    'route'=>'admin.analytics',        'icon'=>'<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>'],
        ];
        $currentRoute = Route::currentRouteName();
        @endphp

        @foreach($navItems as $item)
        @php $isActive = str_starts_with($currentRoute ?? '', rtrim($item['route'], '.index')); @endphp
        <a href="{{ route($item['route']) }}" class="sidebar-link {{ $isActive ? 'active' : '' }}">
            {!! $item['icon'] !!}
            {{ $item['label'] }}
        </a>
        @endforeach
    </nav>

    <div style="position:absolute;bottom:1.5rem;left:0;right:0;padding:0 0.75rem;">
        <a href="{{ route('home') }}" class="sidebar-link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Back to Site
        </a>
        <form method="POST" action="{{ route('logout') }}" style="margin-top:0.25rem;">
            @csrf
            <button type="submit" class="sidebar-link" style="background:none;border:none;width:100%;text-align:left;cursor:pointer;color:inherit;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Logout
            </button>
        </form>
    </div>
</aside>
