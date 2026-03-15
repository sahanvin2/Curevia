<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO Meta Tags --}}
    <title>@yield('title', 'Curevia.app — The Ocean of Knowledge')</title>
    <meta name="description" content="@yield('meta_description', 'Curevia.app is a futuristic knowledge encyclopedia covering space, science, history, geography, animals, mythology, civilizations, and natural wonders.')">
    <meta name="keywords" content="@yield('meta_keywords', 'curevia.app, curevia, encyclopedia, knowledge, science, space, history, animals, geography, mythology, civilizations')">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    {{-- Open Graph / Twitter --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title', 'Curevia.app — The Ocean of Knowledge')">
    <meta property="og:description" content="@yield('meta_description', 'Explore the universe of knowledge with Curevia.app.')">
    <meta property="og:image" content="@yield('og_image', asset('images/og-default.jpg'))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="Curevia.app">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Curevia.app — The Ocean of Knowledge')">
    <meta name="twitter:description" content="@yield('meta_description', 'Explore the universe of knowledge with Curevia.app.')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/og-default.jpg'))">

    {{-- Schema Markup --}}
    @yield('schema_markup')

    {{-- Fonts (async to avoid render-blocking) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'" href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800;0,14..32,900;1,14..32,400&display=swap">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800;0,14..32,900;1,14..32,400&display=swap"></noscript>

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Extra head --}}
    @yield('extra_head')
</head>
<body class="antialiased {{ request()->routeIs('home') ? 'page-home' : '' }}" style="background:#0B0F14; color:#F0F4FF;">

    {{-- Stars Background --}}
    <div id="stars-bg" class="stars-container" aria-hidden="true"></div>

    {{-- Nebula Orbs --}}
    <div aria-hidden="true" style="position:fixed;inset:0;pointer-events:none;z-index:0;overflow:hidden;">
        <div class="nebula-orb" style="width:500px;height:500px;top:-100px;right:-150px;background:radial-gradient(circle,rgba(124,108,255,0.07),transparent 70%);"></div>
        <div class="nebula-orb" style="width:600px;height:600px;bottom:-200px;left:-200px;background:radial-gradient(circle,rgba(34,242,226,0.05),transparent 70%);animation-delay:-7s;"></div>
        <div class="nebula-orb" style="width:400px;height:400px;top:40%;left:50%;transform:translateX(-50%);background:radial-gradient(circle,rgba(45,212,191,0.04),transparent 70%);animation-delay:-14s;"></div>
    </div>

    {{-- ═══════ NAVBAR ═══════ --}}
    <nav id="navbar" class="navbar transparent" role="navigation" aria-label="Main navigation">
        <div style="max-width:1280px;margin:0 auto;padding:0 1.5rem;display:flex;align-items:center;justify-content:space-between;">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="logo-text" style="display:flex;align-items:center;gap:0.75rem;text-decoration:none;" aria-label="Curevia Home">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                    <circle cx="16" cy="16" r="14" stroke="#22F2E2" stroke-width="1.5" opacity="0.3"/>
                    <circle cx="16" cy="16" r="8" stroke="#22F2E2" stroke-width="1.5"/>
                    <circle cx="16" cy="16" r="3" fill="#22F2E2"/>
                    <line x1="16" y1="2" x2="16" y2="8" stroke="#22F2E2" stroke-width="1.5" stroke-linecap="round"/>
                    <line x1="16" y1="24" x2="16" y2="30" stroke="#22F2E2" stroke-width="1.5" stroke-linecap="round"/>
                    <line x1="2" y1="16" x2="8" y2="16" stroke="#22F2E2" stroke-width="1.5" stroke-linecap="round"/>
                    <line x1="24" y1="16" x2="30" y2="16" stroke="#22F2E2" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <span>Curevia</span>
            </a>

            {{-- Desktop Navigation --}}
            <div class="desktop-nav" role="menubar">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" role="menuitem">Home</a>
                <a href="{{ route('encyclopedia.index') }}" class="nav-link {{ request()->routeIs('encyclopedia.*') ? 'active' : '' }}" role="menuitem">Encyclopedia</a>
                <a href="{{ route('stories.index') }}" class="nav-link {{ request()->routeIs('stories.*') ? 'active' : '' }}" role="menuitem">Stories</a>
                <a href="{{ route('discover') }}" class="nav-link {{ request()->routeIs('discover') ? 'active' : '' }}" role="menuitem">Discover</a>
                <a href="{{ route('shop.index') }}" class="nav-link {{ request()->routeIs('shop.*') ? 'active' : '' }}" role="menuitem">Shop</a>
                <a href="{{ route('chat') }}" class="nav-link {{ request()->routeIs('chat') ? 'active' : '' }}" role="menuitem" style="display:flex;align-items:center;gap:0.35rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.7;"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                    AI Chat
                </a>
            </div>

            {{-- Right Side --}}
            <div style="display:flex;align-items:center;gap:0.75rem;">

                {{-- Search Toggle --}}
                <button id="nav-search-toggle" class="nav-icon-btn" aria-label="Toggle search (Ctrl+K)" aria-expanded="false" aria-controls="nav-search-bar" title="Search  Ctrl+K">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                </button>

                @auth
                    {{-- User Dropdown --}}
                    <div style="position:relative;" id="user-menu-wrapper">
                        <button id="user-menu-toggle" class="nav-avatar-btn" aria-label="User menu" aria-haspopup="true" aria-expanded="false">
                            <div class="nav-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                            <span class="desktop-only-inline" style="font-size:0.8rem;color:var(--text-secondary);font-weight:500;max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ auth()->user()->name }}</span>
                            <svg id="user-menu-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="color:var(--text-muted);transition:transform .25s ease;flex-shrink:0;" class="desktop-only-inline"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div id="user-dropdown" class="user-dropdown" role="menu" aria-label="User options">
                            <div class="user-dropdown-header">
                                <div class="nav-avatar" style="width:40px;height:40px;font-size:0.95rem;flex-shrink:0;">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                                <div style="min-width:0;">
                                    <div style="font-size:0.875rem;font-weight:700;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->name }}</div>
                                    <div style="font-size:0.72rem;color:var(--text-muted);margin-top:0.1rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->email }}</div>
                                </div>
                            </div>
                            <div class="user-dropdown-divider"></div>
                            <a href="{{ route('dashboard') }}" class="user-dropdown-item" role="menuitem">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                                Dashboard
                            </a>
                            @if(in_array(auth()->user()->role, ['admin', 'contributor']))
                            <a href="{{ route('edits.author.inbox') }}" class="user-dropdown-item" role="menuitem">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                Author Edit Inbox
                            </a>
                            @endif
                            @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.edit-suggestions.index') }}" class="user-dropdown-item" role="menuitem">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10"/><path d="M7 12h6"/></svg>
                                Admin Edit Queue
                            </a>
                            @endif
                            <div class="user-dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}" style="display:block;">
                                @csrf
                                <button type="submit" class="user-dropdown-item user-dropdown-logout" role="menuitem">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn-secondary" style="padding:0.45rem 1.1rem;font-size:0.8rem;">Login</a>
                    <a href="{{ route('register') }}" class="btn-primary desktop-only" style="padding:0.45rem 1.1rem;font-size:0.8rem;">Join Free</a>
                @endauth

                {{-- Mobile hamburger --}}
                <button onclick="toggleMobileMenu()" id="hamburger-btn" aria-label="Open menu" aria-expanded="false" class="nav-icon-btn hamburger-mobile">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                        <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Inline Search Bar (slide-down) --}}
        <div id="nav-search-bar" class="nav-search-slide" role="search" aria-label="Site search" aria-hidden="true">
            <div style="max-width:860px;margin:0 auto;">
                <form action="{{ route('encyclopedia.index') }}" method="GET" style="position:relative;">
                    @if(request()->routeIs('encyclopedia.index') && request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    <div style="position:relative;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true" style="position:absolute;left:1.1rem;top:50%;transform:translateY(-50%);color:var(--text-muted);pointer-events:none;z-index:1;"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>
                        <input type="text" name="q" id="live-search-input" placeholder="Search topics — Space, History, Animals, Science..." class="search-bar" style="border-radius:0.75rem;padding:0.85rem 9rem 0.85rem 3rem;" autocomplete="off" aria-label="Search topics">
                        <button type="submit" aria-label="Search" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:linear-gradient(135deg,var(--accent-cyan),var(--accent-violet));border:none;border-radius:0.5rem;padding:0.55rem 1.1rem;cursor:pointer;color:var(--bg-primary);font-size:0.8rem;font-weight:700;display:flex;align-items:center;gap:0.4rem;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>
                            Search
                        </button>
                    </div>
                    <div id="live-search-results" style="display:none;position:absolute;top:calc(100% + 8px);left:0;right:0;background:#090D12;border:1px solid rgba(34,242,226,0.22);border-radius:0.875rem;max-height:480px;overflow-y:auto;z-index:2000;box-shadow:0 28px 70px rgba(0,0,0,0.85),0 0 0 1px rgba(34,242,226,0.06);"></div>
                </form>
                <div style="display:flex;align-items:center;gap:0.65rem;margin-top:0.65rem;flex-wrap:wrap;">
                    <span style="font-size:0.72rem;color:var(--text-muted);">Popular:</span>
                    @foreach(['Black Holes', 'Ancient Egypt', 'Human Brain', 'Milky Way', 'Amazon Rainforest'] as $hint)
                        <a href="{{ route('encyclopedia.index', array_filter(['q' => $hint, 'category' => request()->routeIs('encyclopedia.index') ? request('category') : null])) }}" style="font-size:0.72rem;color:var(--text-secondary);background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);border-radius:100px;padding:0.2rem 0.7rem;text-decoration:none;transition:all .2s;" onmouseover="this.style.color='var(--accent-cyan)';this.style.borderColor='rgba(34,242,226,0.3)'" onmouseout="this.style.color='var(--text-secondary)';this.style.borderColor='rgba(255,255,255,0.07)'">{{ $hint }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </nav>

    {{-- Mobile Menu Overlay --}}
    <div id="mobile-menu" class="mobile-menu" role="dialog" aria-modal="true" aria-label="Mobile navigation">
        <button onclick="toggleMobileMenu()" aria-label="Close menu" class="nav-icon-btn" style="position:absolute;top:1.25rem;right:1.25rem;padding:0.6rem;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>

        @auth
            <div style="display:flex;flex-direction:column;align-items:center;gap:0.5rem;margin-bottom:1.75rem;">
                <div class="nav-avatar" style="width:56px;height:56px;font-size:1.25rem;border-width:2px;">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div style="font-size:1rem;font-weight:700;color:var(--text-primary);">{{ auth()->user()->name }}</div>
                <div style="font-size:0.78rem;color:var(--text-muted);">{{ auth()->user()->email }}</div>
            </div>
        @endauth

        <a href="{{ route('home') }}" class="mobile-nav-link" onclick="toggleMobileMenu()">Home</a>
        <a href="{{ route('encyclopedia.index') }}" class="mobile-nav-link" onclick="toggleMobileMenu()">Encyclopedia</a>
        <a href="{{ route('stories.index') }}" class="mobile-nav-link" onclick="toggleMobileMenu()">Stories</a>
        <a href="{{ route('discover') }}" class="mobile-nav-link" onclick="toggleMobileMenu()">Discover</a>
        <a href="{{ route('shop.index') }}" class="mobile-nav-link" onclick="toggleMobileMenu()">Shop</a>
        <a href="{{ route('chat') }}" class="mobile-nav-link" onclick="toggleMobileMenu()">AI Chat</a>
        @auth
            @if(in_array(auth()->user()->role, ['admin', 'contributor']))
                <a href="{{ route('edits.author.inbox') }}" class="mobile-nav-link" onclick="toggleMobileMenu()">Author Edit Inbox</a>
            @endif
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.edit-suggestions.index') }}" class="mobile-nav-link" onclick="toggleMobileMenu()">Admin Edit Queue</a>
            @endif
        @endauth

        @auth
            <div style="display:flex;flex-direction:column;align-items:center;gap:0.75rem;margin-top:1.75rem;width:100%;max-width:240px;">
                <a href="{{ route('dashboard') }}" class="btn-secondary" style="width:100%;justify-content:center;" onclick="toggleMobileMenu()">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}" style="width:100%;">
                    @csrf
                    <button type="submit" class="btn-primary" style="width:100%;justify-content:center;">Sign Out</button>
                </form>
            </div>
        @else
            <div style="display:flex;gap:1rem;margin-top:1.75rem;">
                <a href="{{ route('login') }}" class="btn-secondary">Login</a>
                <a href="{{ route('register') }}" class="btn-primary">Join Free</a>
            </div>
        @endauth
    </div>

    {{-- Main Content --}}
    <main id="main-content">
        @yield('content')
    </main>

    {{-- ═══════ FOOTER ═══════ --}}
    <footer style="position:relative;padding:5rem 0 2.5rem;margin-top:6rem;">
        <div style="max-width:1280px;margin:0 auto;padding:0 1.5rem;">

            <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr;gap:3rem;margin-bottom:3.5rem;" class="grid-cols-footer">

                {{-- Brand --}}
                <div>
                    <a href="{{ route('home') }}" class="logo-text" style="font-size:1.75rem;text-decoration:none;display:block;margin-bottom:1rem;">Curevia</a>
                    <p style="color:var(--text-muted);font-size:0.875rem;line-height:1.75;max-width:280px;margin-bottom:1.5rem;">
                        A futuristic knowledge encyclopedia designed to make the universe of knowledge accessible to everyone.
                    </p>
                    <div style="display:flex;gap:0.75rem;">
                        @foreach([
                            ['Twitter/X', 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.748l7.73-8.835L1.254 2.25H8.08l4.264 5.633zm-1.161 17.52h1.833L7.084 4.126H5.117z'],
                        ] as [$name, $path])
                        <a href="#" aria-label="{{ $name }}" style="width:36px;height:36px;border-radius:50%;background:rgba(34,242,226,0.06);border:1px solid rgba(34,242,226,0.15);display:flex;align-items:center;justify-content:center;color:var(--text-muted);transition:all .3s ease;text-decoration:none;" onmouseover="this.style.borderColor='var(--accent-cyan)';this.style.color='var(--accent-cyan)'" onmouseout="this.style.borderColor='rgba(34,242,226,0.15)';this.style.color='var(--text-muted)'">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="{{ $path }}"/></svg>
                        </a>
                        @endforeach
                        <a href="#" aria-label="GitHub" style="width:36px;height:36px;border-radius:50%;background:rgba(34,242,226,0.06);border:1px solid rgba(34,242,226,0.15);display:flex;align-items:center;justify-content:center;color:var(--text-muted);transition:all .3s ease;" onmouseover="this.style.borderColor='var(--accent-cyan)';this.style.color='var(--accent-cyan)'" onmouseout="this.style.borderColor='rgba(34,242,226,0.15)';this.style.color='var(--text-muted)'">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.373 0 12c0 5.303 3.438 9.8 8.205 11.387.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61-.546-1.387-1.333-1.756-1.333-1.756-1.09-.745.083-.73.083-.73 1.205.085 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 21.795 24 17.295 24 12c0-6.627-5.373-12-12-12z"/></svg>
                        </a>
                    </div>
                </div>

                {{-- Explore --}}
                <div>
                    <h4 style="font-size:0.8rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:1.25rem;">Explore</h4>
                    @foreach(['Space' => 'space', 'Earth' => 'earth', 'Science' => 'science', 'History' => 'history', 'Animals' => 'animals'] as $label => $slug)
                    <a href="{{ route('encyclopedia.index', ['category' => $slug]) }}" class="footer-link">{{ $label }}</a>
                    @endforeach
                </div>

                {{-- Knowledge --}}
                <div>
                    <h4 style="font-size:0.8rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:1.25rem;">Knowledge</h4>
                    @foreach(['Human Body' => 'human-body', 'Countries' => 'countries', 'Mythology' => 'mythology', 'Zodiac' => 'zodiac', 'Technology' => 'technology'] as $label => $slug)
                    <a href="{{ route('encyclopedia.index', ['category' => $slug]) }}" class="footer-link">{{ $label }}</a>
                    @endforeach
                </div>

                {{-- Platform --}}
                <div>
                    <h4 style="font-size:0.8rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:1.25rem;">Platform</h4>
                    <a href="{{ route('stories.index') }}" class="footer-link">Stories</a>
                    <a href="{{ route('discover') }}" class="footer-link">Discover</a>
                    <a href="{{ route('shop.index') }}" class="footer-link">Shop</a>
                    <a href="{{ route('contributors.index') }}" class="footer-link">Contributors</a>
                </div>

                {{-- Company --}}
                <div>
                    <h4 style="font-size:0.8rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:1.25rem;">Company</h4>
                    <a href="{{ route('about') }}" class="footer-link">About</a>
                    <a href="{{ route('privacy') }}" class="footer-link">Privacy Policy</a>
                    <a href="{{ route('terms') }}" class="footer-link">Terms of Use</a>
                    <a href="{{ route('contact') }}" class="footer-link">Contact</a>
                </div>
            </div>

            <hr class="glow-divider" style="margin:2rem 0;">

            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
                <p style="color:var(--text-muted);font-size:0.8rem;">
                    © {{ date('Y') }} Curevia. All rights reserved. The Ocean of Knowledge.
                </p>
                <p style="color:var(--text-muted);font-size:0.75rem;display:flex;align-items:center;gap:0.4rem;">
                    <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:var(--accent-emerald);animation:pulse-glow 2s infinite;"></span>
                    All systems operational
                </p>
            </div>
        </div>
    </footer>

    @include('layouts._share_modal')

    @yield('extra_scripts')

    {{-- ═══════ CUREVIA AI CHATBOT ═══════ --}}
    <div id="curevia-chatbot" class="chatbot-wrapper" aria-label="Curevia AI Chatbot">

        {{-- Floating Toggle Button --}}
        <button id="chatbot-toggle" class="chatbot-fab" aria-label="Open Curevia AI chatbot" title="Ask Curevia AI">
            <span class="chatbot-fab-pulse"></span>
            <svg id="chatbot-icon-open" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
            </svg>
            <svg id="chatbot-icon-close" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:none;">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>

        {{-- Chat Panel --}}
        <div id="chatbot-panel" class="chatbot-panel" role="dialog" aria-modal="false" aria-label="Chat with Curevia AI">

            {{-- Header --}}
            <div class="chatbot-header">
                <div style="display:flex;align-items:center;gap:0.65rem;">
                    <div class="chatbot-avatar">
                        <svg width="20" height="20" viewBox="0 0 32 32" fill="none">
                            <circle cx="16" cy="16" r="8" stroke="#22F2E2" stroke-width="1.5"/>
                            <circle cx="16" cy="16" r="3" fill="#22F2E2"/>
                            <line x1="16" y1="2" x2="16" y2="8" stroke="#22F2E2" stroke-width="1.5" stroke-linecap="round"/>
                            <line x1="16" y1="24" x2="16" y2="30" stroke="#22F2E2" stroke-width="1.5" stroke-linecap="round"/>
                            <line x1="2" y1="16" x2="8" y2="16" stroke="#22F2E2" stroke-width="1.5" stroke-linecap="round"/>
                            <line x1="24" y1="16" x2="30" y2="16" stroke="#22F2E2" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div>
                        <div style="font-size:0.9rem;font-weight:700;color:var(--text-primary);line-height:1.2;">Curevia AI</div>
                        <div id="chatbot-status" style="font-size:0.68rem;color:#2DD4BF;display:flex;align-items:center;gap:0.35rem;">
                            <span style="width:5px;height:5px;border-radius:50%;background:#2DD4BF;display:inline-block;"></span>
                            Online — Ask me anything
                        </div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:0.25rem;">
                    <button id="chatbot-clear" class="chatbot-header-btn" aria-label="Clear chat" title="Clear chat">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="m19 6-.867 12.142A2 2 0 0 1 16.138 20H7.862a2 2 0 0 1-1.995-1.858L5 6"/></svg>
                    </button>
                    <button id="chatbot-close" class="chatbot-header-btn" aria-label="Close chat" title="Close chat">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
            </div>

            {{-- Messages Area --}}
            <div id="chatbot-messages" class="chatbot-messages">
                {{-- Welcome message --}}
                <div class="chatbot-msg assistant">
                    <div class="chatbot-msg-avatar">
                        <svg width="14" height="14" viewBox="0 0 32 32" fill="none"><circle cx="16" cy="16" r="8" stroke="#22F2E2" stroke-width="1.5"/><circle cx="16" cy="16" r="3" fill="#22F2E2"/></svg>
                    </div>
                    <div class="chatbot-bubble assistant">
                        <strong>Welcome to Curevia AI!</strong> 🌌<br><br>
                        I'm your knowledge companion. Ask me about:<br>
                        <span style="color:var(--accent-cyan);">🚀</span> Space & Astronomy<br>
                        <span style="color:var(--accent-cyan);">🧬</span> Science & Technology<br>
                        <span style="color:var(--accent-cyan);">🏛️</span> History & Civilizations<br>
                        <span style="color:var(--accent-cyan);">🌍</span> Geography & Nature<br>
                        <span style="color:var(--accent-cyan);">🐾</span> Animals & Biology<br><br>
                        <em style="color:var(--text-muted);font-size:0.82em;">Try: "Tell me about black holes" or "Explain photosynthesis"</em>
                    </div>
                </div>
            </div>

            {{-- Quick Suggestion Chips --}}
            <div id="chatbot-suggestions" class="chatbot-suggestions">
                <button class="chatbot-chip" data-q="Tell me about black holes">🌑 Black Holes</button>
                <button class="chatbot-chip" data-q="Explain how the human brain works">🧠 Human Brain</button>
                <button class="chatbot-chip" data-q="What was Ancient Egypt like?">🏛️ Ancient Egypt</button>
                <button class="chatbot-chip" data-q="How far away is the nearest star?">⭐ Nearest Star</button>
            </div>

            {{-- Input Area --}}
            <div class="chatbot-input-area">
                <div class="chatbot-input-row">
                    <textarea id="chatbot-input" class="chatbot-textarea" placeholder="Ask Curevia AI anything..." rows="1" maxlength="{{ auth()->check() ? 12000 : 2000 }}" aria-label="Type your message"></textarea>
                    <button id="chatbot-send" class="chatbot-send-btn" aria-label="Send message" disabled>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                        </svg>
                    </button>
                </div>
                <div style="font-size:0.62rem;color:var(--text-muted);text-align:center;padding-top:0.35rem;">
                    Powered by Gemini · Curevia AI may make mistakes
                </div>
            </div>
        </div>
    </div>
    {{-- ═══════ COOKIE CONSENT BANNER ═══════ --}}
    <div id="cookie-banner" role="dialog" aria-modal="true" aria-label="Cookie consent">
        <div class="cookie-inner">
            <div style="display:flex;align-items:flex-start;gap:1rem;flex-wrap:wrap;">

                {{-- Icon + Text --}}
                <div style="flex:1;min-width:260px;">
                    <div style="display:flex;align-items:center;gap:0.65rem;margin-bottom:0.75rem;">
                        <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,rgba(34,242,226,0.15),rgba(124,108,255,0.1));border:1px solid rgba(34,242,226,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#22F2E2" stroke-width="1.8" stroke-linecap="round">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10c0-.34-.02-.67-.05-1"/>
                                <path d="M17 3.34A10 10 0 0 1 21.65 8"/>
                                <circle cx="15" cy="7" r="1" fill="#22F2E2" stroke="none"/>
                                <circle cx="19" cy="11" r="1" fill="#22F2E2" stroke="none"/>
                                <circle cx="9" cy="9" r="1" fill="#7C6CFF" stroke="none"/>
                                <circle cx="7" cy="14" r="1" fill="#7C6CFF" stroke="none"/>
                                <circle cx="11" cy="17" r="1" fill="#22F2E2" stroke="none"/>
                            </svg>
                        </div>
                        <div>
                            <h4 style="font-size:0.95rem;font-weight:800;color:var(--text-primary);margin:0;line-height:1.2;">Your Privacy on Curevia</h4>
                            <p style="font-size:0.7rem;color:var(--text-muted);margin:0.1rem 0 0;">We use cookies to improve your knowledge journey 🌌</p>
                        </div>
                    </div>
                    <p style="font-size:0.78rem;color:#94A3B8;line-height:1.65;margin:0 0 0.9rem;">
                        Curevia uses cookies to keep the site working, understand how you explore knowledge, and personalize your experience. We never sell your data. By clicking <strong style="color:var(--text-primary);">Accept All</strong>, you agree to our
                        <a href="{{ route('privacy') }}" style="color:#22F2E2;text-decoration:none;">Privacy Policy</a> and
                        <a href="{{ route('terms') }}" style="color:#22F2E2;text-decoration:none;">Terms of Use</a>.
                    </p>

                    {{-- Cookie types --}}
                    <div class="cookie-types-row" style="display:flex;flex-wrap:wrap;gap:0.5rem;">
                        <span class="cookie-type-pill" style="background:rgba(34,242,226,0.08);border:1px solid rgba(34,242,226,0.2);color:#22F2E2;">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            Essential
                        </span>
                        <span class="cookie-type-pill" style="background:rgba(124,108,255,0.08);border:1px solid rgba(124,108,255,0.2);color:#7C6CFF;">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                            Analytics
                        </span>
                        <span class="cookie-type-pill" style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);color:#F59E0B;">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg>
                            Personalization
                        </span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="cookie-actions" style="display:flex;flex-direction:column;gap:0.6rem;flex-shrink:0;justify-content:center;padding-top:0.25rem;">
                    <button class="cookie-btn cookie-btn-accept" id="cookie-accept-all" onclick="cookieConsent('all')">
                        ✓ Accept All
                    </button>
                    <button class="cookie-btn cookie-btn-manage" id="cookie-manage" onclick="cookieConsent('essential')">
                        ⚙ Essential Only
                    </button>
                    <button class="cookie-btn cookie-btn-reject" onclick="cookieConsent('reject')">
                        ✕ Reject
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div
        id="curevia-chat-config"
        hidden
        data-is-authenticated="{{ auth()->check() ? '1' : '0' }}"
        data-context-limit-chars="{{ auth()->check() ? 100000 : 2000 }}"
        data-login-url="{{ route('login') }}"
        data-register-url="{{ route('register') }}"
        data-cookie-consent-url="{{ route('api.cookie-consent') }}"
    ></div>

    <script>
    (function() {
        var configEl = document.getElementById('curevia-chat-config');
        var fallbackConfig = {
            isAuthenticated: false,
            contextLimitChars: 2000,
            loginUrl: '/login',
            registerUrl: '/register'
        };

        if (!configEl) {
            window.CureviaChatConfig = fallbackConfig;
            return;
        }

        var limitValue = parseInt(configEl.getAttribute('data-context-limit-chars') || '', 10);
        window.CureviaChatConfig = {
            isAuthenticated: configEl.getAttribute('data-is-authenticated') === '1',
            contextLimitChars: Number.isFinite(limitValue) ? limitValue : fallbackConfig.contextLimitChars,
            loginUrl: configEl.getAttribute('data-login-url') || fallbackConfig.loginUrl,
            registerUrl: configEl.getAttribute('data-register-url') || fallbackConfig.registerUrl
        };
    })();

    (function() {
        var COOKIE_KEY = 'curevia_cookie_consent';
        var banner = document.getElementById('cookie-banner');
        var configEl = document.getElementById('curevia-chat-config');

        function cookieConsent(choice) {
            try { localStorage.setItem(COOKIE_KEY, choice + ':' + Date.now()); } catch(e){}
            // Store consent securely on the server
            var consentUrl = (window.CureviaChatConfig && configEl)
                ? (configEl.getAttribute('data-cookie-consent-url') || '')
                : '';
            if (consentUrl) {
                fetch(consentUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ consent: choice })
                }).catch(function(){});
            }
            banner.classList.remove('visible');
            setTimeout(function(){ banner.style.display = 'none'; }, 500);
        }
        window.cookieConsent = cookieConsent;

        function shouldShow() {
            try {
                var v = localStorage.getItem(COOKIE_KEY);
                if (!v) return true;
                var ts = parseInt((v.split(':')[1]) || '0', 10);
                // Re-ask after 365 days
                return Date.now() - ts > 365 * 24 * 60 * 60 * 1000;
            } catch(e) { return true; }
        }

        if (shouldShow()) {
            setTimeout(function() { banner.classList.add('visible'); }, 1200);
        } else {
            banner.style.display = 'none';
        }
    })();
    </script>
</body>
</html>

<style>
@media (max-width: 768px) {
    .grid-cols-footer { grid-template-columns: 1fr 1fr !important; }
}
@media (max-width: 480px) {
    .grid-cols-footer { grid-template-columns: 1fr !important; }
}

/* ═══ Cookie Consent ═══ */
#cookie-banner {
    position: fixed;
    bottom: 0; left: 0; right: 0;
    z-index: 99999;
    transform: translateY(100%);
    transition: transform 0.45s cubic-bezier(0.34,1.56,0.64,1);
    padding: 0 1rem 1rem;
}
#cookie-banner.visible { transform: translateY(0); }
.cookie-inner {
    max-width: 900px;
    margin: 0 auto;
    background: rgba(11,15,20,0.92);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1px solid rgba(34,242,226,0.18);
    border-radius: 18px;
    box-shadow: 0 -8px 48px rgba(34,242,226,0.06), 0 24px 64px rgba(0,0,0,0.6);
    padding: 1.5rem 1.75rem;
    position: relative;
    overflow: hidden;
}
.cookie-inner::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, #22F2E2, #7C6CFF, #22F2E2, transparent);
    animation: shimmer-line 3s infinite linear;
}
@keyframes shimmer-line {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}
.cookie-type-pill {
    display: inline-flex; align-items: center; gap: 0.35rem;
    font-size: 0.72rem; font-weight: 600;
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    cursor: default;
}
.cookie-btn {
    padding: 0.6rem 1.35rem;
    border-radius: 10px;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    border: none;
    transition: all 0.25s ease;
    white-space: nowrap;
}
.cookie-btn-accept {
    background: linear-gradient(135deg, #22F2E2, #7C6CFF);
    color: #0B0F14;
}
.cookie-btn-accept:hover { opacity: 0.88; transform: translateY(-1px); }
.cookie-btn-manage {
    background: rgba(34,242,226,0.06);
    border: 1px solid rgba(34,242,226,0.25) !important;
    color: var(--accent-cyan, #22F2E2);
}
.cookie-btn-manage:hover { background: rgba(34,242,226,0.12); }
.cookie-btn-reject {
    background: transparent;
    border: 1px solid rgba(255,255,255,0.08) !important;
    color: var(--text-muted, #64748B);
    font-weight: 500;
}
.cookie-btn-reject:hover { border-color: rgba(255,255,255,0.2) !important; color: #94A3B8; }
@media (max-width: 640px) {
    .cookie-inner { padding: 1.2rem 1.1rem; border-radius: 14px; }
    .cookie-actions { flex-direction: column !important; }
    .cookie-actions .cookie-btn { width: 100%; text-align: center; }
    .cookie-types-row { flex-direction: column !important; gap: 0.5rem !important; }
}
</style>
