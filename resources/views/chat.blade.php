<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Curevia AI — Chat</title>
    <meta name="description" content="Chat with Curevia AI — your intelligent knowledge companion.">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800;0,14..32,900;1,14..32,400&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased" style="background:#0B0F14;color:#F0F4FF;overflow:hidden;">

<div class="chat-page">

    {{-- ═══════ SIDEBAR ═══════ --}}
    <aside id="chat-sidebar" class="chat-sidebar">

        {{-- Sidebar Top --}}
        <div class="chat-sidebar-top">
            <a href="{{ route('home') }}" class="chat-logo" aria-label="Back to Curevia">
                <svg width="22" height="22" viewBox="0 0 32 32" fill="none">
                    <circle cx="16" cy="16" r="8" stroke="#22F2E2" stroke-width="1.5"/>
                    <circle cx="16" cy="16" r="3" fill="#22F2E2"/>
                    <line x1="16" y1="2" x2="16" y2="8" stroke="#22F2E2" stroke-width="1.5" stroke-linecap="round"/>
                    <line x1="16" y1="24" x2="16" y2="30" stroke="#22F2E2" stroke-width="1.5" stroke-linecap="round"/>
                    <line x1="2" y1="16" x2="8" y2="16" stroke="#22F2E2" stroke-width="1.5" stroke-linecap="round"/>
                    <line x1="24" y1="16" x2="30" y2="16" stroke="#22F2E2" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <span>Curevia AI</span>
            </a>
        </div>

        {{-- New Chat Button --}}
        <div style="padding:0 0.75rem 0.5rem;">
            <button id="chat-new-btn" class="chat-new-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Chat
            </button>
        </div>

        {{-- Conversation List --}}
        <div id="chat-conversations" class="chat-conversations"></div>

        {{-- Sidebar Footer --}}
        <div class="chat-sidebar-footer">
            @auth
                <div class="chat-sidebar-user">
                    <div class="chat-user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <span class="chat-user-name">{{ auth()->user()->name }}</span>
                </div>
            @endauth
            <a href="{{ route('home') }}" class="chat-back-link">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="m15 18-6-6 6-6"/></svg>
                Back to Curevia
            </a>
        </div>
    </aside>

    {{-- ═══════ MAIN ═══════ --}}
    <main class="chat-main">

        {{-- Top Bar --}}
        <header class="chat-topbar">
            <button id="chat-sidebar-toggle" class="chat-topbar-btn" aria-label="Toggle sidebar">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                    <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>
            <div class="chat-topbar-model">
                <svg width="14" height="14" viewBox="0 0 32 32" fill="none" style="opacity:0.7;">
                    <circle cx="16" cy="16" r="8" stroke="#22F2E2" stroke-width="1.5"/>
                    <circle cx="16" cy="16" r="3" fill="#22F2E2"/>
                </svg>
                <span id="chat-topbar-title">Curevia AI</span>
            </div>
            <button id="chat-new-mobile" class="chat-topbar-btn" title="New Chat">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </button>
        </header>

        {{-- Messages --}}
        <div id="chat-messages" class="chat-messages-area">

            {{-- Spacer: grows when few messages, shrinks to 0 when scrollable --}}
            <div id="chat-msgs-anchor" class="chat-msgs-anchor"></div>

            {{-- Welcome --}}
            <div id="chat-welcome" class="chat-welcome">
                <div class="chat-welcome-orb">
                    <div class="chat-welcome-orb-inner">
                        <svg width="40" height="40" viewBox="0 0 32 32" fill="none">
                            <circle cx="16" cy="16" r="14" stroke="#22F2E2" stroke-width="0.8" opacity="0.25"/>
                            <circle cx="16" cy="16" r="8" stroke="#22F2E2" stroke-width="1.5"/>
                            <circle cx="16" cy="16" r="3" fill="#22F2E2"/>
                            <line x1="16" y1="2" x2="16" y2="8" stroke="#22F2E2" stroke-width="1.5" stroke-linecap="round"/>
                            <line x1="16" y1="24" x2="16" y2="30" stroke="#22F2E2" stroke-width="1.5" stroke-linecap="round"/>
                            <line x1="2" y1="16" x2="8" y2="16" stroke="#22F2E2" stroke-width="1.5" stroke-linecap="round"/>
                            <line x1="24" y1="16" x2="30" y2="16" stroke="#22F2E2" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <h1 class="chat-welcome-title">What would you like to explore?</h1>
                <p class="chat-welcome-sub">Ask me anything about science, space, history, nature, or the wonders of our universe.</p>

                <div class="chat-welcome-grid">
                    <button class="chat-welcome-card" data-q="Tell me about black holes — how do they form and what happens inside?">
                        <div class="chat-card-top">
                            <span class="chat-card-emoji">🌑</span>
                            <span class="chat-card-title">Black Holes</span>
                        </div>
                        <span class="chat-card-desc">How do they form and what happens inside?</span>
                    </button>
                    <button class="chat-welcome-card" data-q="Explain how the human brain stores and retrieves memories">
                        <div class="chat-card-top">
                            <span class="chat-card-emoji">🧠</span>
                            <span class="chat-card-title">Human Brain</span>
                        </div>
                        <span class="chat-card-desc">How does it store and retrieve memories?</span>
                    </button>
                    <button class="chat-welcome-card" data-q="What was daily life like in Ancient Egypt? How were the pyramids built?">
                        <div class="chat-card-top">
                            <span class="chat-card-emoji">🏛️</span>
                            <span class="chat-card-title">Ancient Egypt</span>
                        </div>
                        <span class="chat-card-desc">Daily life and the mystery of the pyramids</span>
                    </button>
                    <button class="chat-welcome-card" data-q="What are the most fascinating deep-sea creatures ever discovered?">
                        <div class="chat-card-top">
                            <span class="chat-card-emoji">🌊</span>
                            <span class="chat-card-title">Deep Sea</span>
                        </div>
                        <span class="chat-card-desc">The most fascinating creatures of the abyss</span>
                    </button>
                    <button class="chat-welcome-card" data-q="How far is the nearest star and could we ever travel there?">
                        <div class="chat-card-top">
                            <span class="chat-card-emoji">✨</span>
                            <span class="chat-card-title">Nearest Star</span>
                        </div>
                        <span class="chat-card-desc">Could we ever travel to another star system?</span>
                    </button>
                    <button class="chat-welcome-card" data-q="Explain quantum physics in simple terms — what is superposition and entanglement?">
                        <div class="chat-card-top">
                            <span class="chat-card-emoji">⚛️</span>
                            <span class="chat-card-title">Quantum Physics</span>
                        </div>
                        <span class="chat-card-desc">Superposition and entanglement explained simply</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Input --}}
        <div class="chat-input-wrapper">
            <div class="chat-input-container">
                <div class="chat-input-box">
                    <textarea id="chat-input" class="chat-input-field" placeholder="Message Curevia AI..." rows="1" maxlength="2000" aria-label="Type your message"></textarea>
                    <button id="chat-send-btn" class="chat-send" aria-label="Send message" disabled>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
                <p class="chat-disclaimer">Powered by Gemini · Curevia AI can make mistakes. Verify important information.</p>
            </div>
        </div>
    </main>
</div>

{{-- Mobile sidebar overlay --}}
<div id="chat-sidebar-overlay" class="chat-sidebar-overlay"></div>

{{-- ═══════ SHARE AS POST MODAL ═══════ --}}
<div id="share-modal-overlay" class="share-modal-overlay">
    <div class="share-modal">
        <div class="share-modal-header">
            <h2 class="share-modal-title">Share as Post</h2>
            <button id="share-modal-close" class="share-modal-close" aria-label="Close">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="share-modal-body">
            {{-- Post type --}}
            <div class="share-field">
                <label>Post Type</label>
                <div class="share-type-selector">
                    <button class="share-type-btn active" data-type="story">
                        <span class="type-icon">📖</span>
                        <span class="type-label">Story</span>
                    </button>
                    <button class="share-type-btn" data-type="encyclopedia">
                        <span class="type-icon">📚</span>
                        <span class="type-label">Encyclopedia</span>
                    </button>
                </div>
            </div>
            {{-- Title --}}
            <div class="share-field">
                <label for="share-title">Title <span style="color:var(--accent-cyan)">*</span></label>
                <input id="share-title" type="text" placeholder="Give your post a title..." maxlength="255">
            </div>
            {{-- Two-col row: Category + Image --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.65rem;">
                <div class="share-field">
                    <label for="share-category">Category</label>
                    <select id="share-category">
                        <option value="">AI will choose...</option>
                        <option value="Space">🚀 Space</option>
                        <option value="Earth">🌍 Earth</option>
                        <option value="Science">🔬 Science</option>
                        <option value="History">🏛️ History</option>
                        <option value="Animals">🦁 Animals</option>
                        <option value="Human Body">🫀 Human Body</option>
                        <option value="Countries">🗺️ Countries</option>
                        <option value="Nature">🌿 Nature</option>
                        <option value="Mythology">⚡ Mythology</option>
                        <option value="Civilizations">🏺 Civilizations</option>
                        <option value="Technology">💡 Technology</option>
                    </select>
                </div>
                <div class="share-field">
                    <label for="share-image">Image URL <span style="color:var(--text-muted);font-weight:400">(optional)</span></label>
                    <input id="share-image" type="url" placeholder="https://...">
                </div>
            </div>
            {{-- Description --}}
            <div class="share-field">
                <label for="share-description">Description <span style="color:var(--text-muted);font-weight:400">(optional — AI generates if blank)</span></label>
                <textarea id="share-description" placeholder="A brief summary or excerpt for the post..." rows="2" style="max-height:80px"></textarea>
            </div>
            {{-- Quick Facts (encyclopedia only) --}}
            <div id="share-qf-section" class="share-field" style="display:none;">
                <label>Quick Facts <span style="color:var(--text-muted);font-weight:400">(optional — AI generates if blank)</span></label>
                <div id="share-qf-rows"></div>
                <button id="share-qf-add" class="share-qf-add-btn" type="button">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Quick Fact
                </button>
            </div>
            {{-- Content preview --}}
            <div class="share-field">
                <label>Content Preview <span style="color:var(--text-muted);font-weight:400">(AI will expand this into a full post)</span></label>
                <div id="share-preview" class="share-preview"></div>
            </div>
        </div>
        <div class="share-modal-footer">
            <span id="share-status" class="share-status"></span>
            <button id="share-cancel" class="share-cancel-btn">Cancel</button>
            <button id="share-publish" class="share-publish-btn">
                <span id="share-publish-text">Publish</span>
            </button>
        </div>
    </div>
</div>

</body>
</html>
