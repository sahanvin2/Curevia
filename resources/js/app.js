import './bootstrap';

// ═══════════════════════════════════════════
// CUREVIA — Main JavaScript
// ═══════════════════════════════════════════

// ── Global utility (used by both initChatbot & initFullPageChat) ──
function escapeHtml(text) {
    const d = document.createElement('div');
    d.textContent = text;
    return d.innerHTML;
}

/**
 * Robustly extract answer/summary/suggestions from an AI response,
 * handling cases where the backend returns raw JSON as the answer string.
 */
function cleanAiAnswer(raw, fallbackSuggestions = [], fallbackSummary = null) {
    const s = (raw || '').trim();

    // Doesn't look like JSON — return as-is
    if (!s.startsWith('{') || !s.includes('"answer"')) {
        return { answer: raw || '', suggestions: fallbackSuggestions, summary: fallbackSummary };
    }

    // Strategy 1: full JSON parse
    try {
        const p = JSON.parse(s);
        if (p && p.answer) {
            return {
                answer:      p.answer,
                suggestions: Array.isArray(p.suggestions) ? p.suggestions.slice(0, 4) : fallbackSuggestions,
                summary:     p.summary     || fallbackSummary,
            };
        }
    } catch(e) { /* continue */ }

    // Strategy 2: regex-extract "answer" field value
    const m = s.match(/"answer"\s*:\s*"((?:[^"\\]|\\[\s\S])*?)"/);
    if (m) {
        let ans = m[1];
        try { ans = JSON.parse('"' + m[1] + '"'); }
        catch(e) { ans = m[1].replace(/\\n/g, '\n').replace(/\\t/g, '\t').replace(/\\"/g, '"').replace(/\\\\/g, '\\'); }

        // Extract suggestions
        let sugs = fallbackSuggestions;
        const sm = s.match(/"suggestions"\s*:\s*\[([^\]]*)\]/);
        if (sm) {
            sugs = [];
            const re = /"((?:[^"\\]|\\.)*)"/g;
            let gm;
            while ((gm = re.exec(sm[1])) !== null) {
                try { sugs.push(JSON.parse('"' + gm[1] + '"')); }
                catch(e) { sugs.push(gm[1]); }
            }
            sugs = sugs.slice(0, 4);
        }

        // Extract summary
        let summ = fallbackSummary;
        const sumMatch = s.match(/"summary"\s*:\s*"((?:[^"\\]|\\[\s\S])*?)"/);
        if (sumMatch) {
            try { summ = JSON.parse('"' + sumMatch[1] + '"'); }
            catch(e) { summ = sumMatch[1].replace(/\\n/g, '\n').replace(/\\"/g, '"'); }
        }

        return { answer: ans, suggestions: sugs, summary: summ };
    }

    // Strategy 3: give up, keep raw
    return { answer: raw || '', suggestions: fallbackSuggestions, summary: fallbackSummary };
}

document.addEventListener('DOMContentLoaded', () => {

    // — Star Field Generator —
    initStarField();

    // — Navbar Scroll + Search Toggle —
    initNavbar();

    // — User Dropdown —
    initUserDropdown();

    // — Scroll Reveal —
    initScrollReveal();

    // — Search Autocomplete hints —
    initSearchHints();

    // — Live Search —
    initLiveSearch();

    // — Curevia AI Chatbot —
    initChatbot();

    // — Full-Page Chat —
    initFullPageChat();
});

function initStarField() {
    const container = document.getElementById('stars-bg');
    if (!container) return;

    const count = 80;
    for (let i = 0; i < count; i++) {
        const star = document.createElement('div');
        star.className = 'star';
        const size = Math.random() * 2.5 + 0.5;
        star.style.cssText = `
            left: ${Math.random() * 100}%;
            top: ${Math.random() * 100}%;
            width: ${size}px;
            height: ${size}px;
            --duration: ${Math.random() * 4 + 2}s;
            --delay: ${Math.random() * 5}s;
            opacity: ${Math.random() * 0.5 + 0.1};
        `;
        container.appendChild(star);
    }
}

function initNavbar() {
    const navbar = document.getElementById('navbar');
    if (!navbar) return;

    const updateNavbar = () => {
        if (window.scrollY > 60) {
            navbar.classList.add('scrolled');
            navbar.classList.remove('transparent');
        } else {
            navbar.classList.remove('scrolled');
            navbar.classList.add('transparent');
        }
    };
    window.addEventListener('scroll', updateNavbar, { passive: true });
    updateNavbar();

    // — Search toggle (class-based animation) —
    const searchToggle = document.getElementById('nav-search-toggle');
    const searchBar = document.getElementById('nav-search-bar');
    if (searchToggle && searchBar) {
        const openSearch = () => {
            searchBar.classList.add('open');
            searchBar.setAttribute('aria-hidden', 'false');
            searchToggle.setAttribute('aria-expanded', 'true');
            searchToggle.classList.add('active-search');
            // After the slide transition finishes (~350ms), allow overflow so the
            // results dropdown is NOT clipped by the container's overflow:hidden
            clearTimeout(searchBar._oTimer);
            searchBar._oTimer = setTimeout(() => {
                searchBar.style.overflow = 'visible';
            }, 360);
            setTimeout(() => searchBar.querySelector('input')?.focus(), 60);
        };
        const closeSearch = () => {
            // Reset overflow back to hidden BEFORE collapsing so animation stays clean
            clearTimeout(searchBar._oTimer);
            searchBar.style.overflow = '';
            searchBar.classList.remove('open');
            searchBar.setAttribute('aria-hidden', 'true');
            searchToggle.setAttribute('aria-expanded', 'false');
            searchToggle.classList.remove('active-search');
            // Hide results immediately on close
            const results = document.getElementById('live-search-results');
            if (results) results.style.display = 'none';
        };
        searchToggle.addEventListener('click', () => {
            searchBar.classList.contains('open') ? closeSearch() : openSearch();
        });
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                searchBar.classList.contains('open') ? closeSearch() : openSearch();
            }
            if (e.key === 'Escape' && searchBar.classList.contains('open')) closeSearch();
        });
        document.addEventListener('click', (e) => {
            if (searchBar.classList.contains('open')
                && !e.target.closest('#nav-search-bar')
                && !e.target.closest('#nav-search-toggle')) {
                closeSearch();
            }
        });
    }
}

function initUserDropdown() {
    const wrapper  = document.getElementById('user-menu-wrapper');
    const toggle   = document.getElementById('user-menu-toggle');
    const dropdown = document.getElementById('user-dropdown');
    const chevron  = document.getElementById('user-menu-chevron');
    if (!toggle || !dropdown) return;

    const openDD = () => {
        dropdown.classList.add('open');
        toggle.setAttribute('aria-expanded', 'true');
        if (chevron) chevron.style.transform = 'rotate(180deg)';
    };
    const closeDD = () => {
        dropdown.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
        if (chevron) chevron.style.transform = '';
    };
    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.contains('open') ? closeDD() : openDD();
    });
    document.addEventListener('click', (e) => {
        if (wrapper && !wrapper.contains(e.target)) closeDD();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeDD();
    });
}

function initScrollReveal() {
    const elements = document.querySelectorAll('.reveal');
    if (!elements.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                // Stagger delay based on siblings
                const siblings = entry.target.parentElement.querySelectorAll('.reveal');
                const idx = Array.from(siblings).indexOf(entry.target);
                setTimeout(() => {
                    entry.target.classList.add('revealed');
                }, idx * 80);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });

    elements.forEach(el => observer.observe(el));
}

function initSearchHints() {
    const searchInput = document.getElementById('hero-search');
    if (!searchInput) return;

    const hints = [
        'Explore the Ocean of Knowledge...',
        'Search: Black Holes',
        'Search: Ancient Egypt',
        'Search: Amazon Rainforest',
        'Search: Human Brain',
        'Search: Mount Everest',
        'Search: Roman Empire',
        'Search: Milky Way',
        'Search: Zodiac Signs',
        'Search: Deep Ocean',
    ];

    let idx = 0;
    let charIdx = 0;
    let deleting = false;
    let paused = false;

    function typeWriter() {
        if (paused) return;
        const current = hints[idx];

        if (!deleting) {
            searchInput.placeholder = current.substring(0, charIdx + 1);
            charIdx++;
            if (charIdx === current.length) {
                deleting = true;
                paused = true;
                setTimeout(() => { paused = false; }, 2200);
            }
        } else {
            searchInput.placeholder = current.substring(0, charIdx - 1);
            charIdx--;
            if (charIdx === 0) {
                deleting = false;
                idx = (idx + 1) % hints.length;
            }
        }
    }

    setInterval(typeWriter, 80);
}

// Mobile menu toggle
window.toggleMobileMenu = function() {
    const menu = document.getElementById('mobile-menu');
    if (!menu) return;
    menu.classList.toggle('open');
    const isOpen = menu.classList.contains('open');
    document.body.style.overflow = isOpen ? 'hidden' : '';
    const btn = document.getElementById('hamburger-btn');
    if (btn) btn.setAttribute('aria-expanded', String(isOpen));
};

// Add to cart (placeholder)
window.addToCart = function(productId, btn) {
    if (!btn) return;
    const original = btn.innerHTML;
    btn.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Added!`;
    btn.style.background = 'linear-gradient(135deg, #2DD4BF, #22F2E2)';
    setTimeout(() => {
        btn.innerHTML = original;
        btn.style.background = '';
    }, 2000);
};

// Bookmark toggle
window.toggleBookmark = function(btn) {
    if (!btn) return;
    btn.classList.toggle('bookmarked');
    const icon = btn.querySelector('svg');
    if (icon) {
        icon.style.fill = btn.classList.contains('bookmarked') ? '#22F2E2' : 'none';
    }
};

// Live Search
function initLiveSearch() {
    const input = document.getElementById('live-search-input');
    const results = document.getElementById('live-search-results');
    if (!input || !results) return;

    let debounceTimer;
    let controller;

    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const q = input.value.trim();

        if (q.length < 2) {
            results.style.display = 'none';
            results.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(() => {
            if (controller) controller.abort();
            controller = new AbortController();

            fetch(`/api/search?q=${encodeURIComponent(q)}`, { signal: controller.signal })
                .then(r => r.json())
                .then(json => {
                    const data = json.results || json;
                    if (!data.length) {
                        results.innerHTML = '<div style="padding:1.25rem;text-align:center;color:var(--text-muted,#6B7280);font-size:0.875rem;">No results found</div>';
                        results.style.display = 'block';
                        return;
                    }

                    results.innerHTML = data.map(item => `
                        <a href="${item.url}" style="display:flex;align-items:center;gap:1rem;padding:0.75rem 1.25rem;text-decoration:none;transition:background .15s;border-bottom:1px solid rgba(34,242,226,0.06);" onmouseover="this.style.background='rgba(34,242,226,0.06)'" onmouseout="this.style.background='none'">
                            ${item.img ? `<img src="${item.img}" alt="" style="width:48px;height:48px;border-radius:0.5rem;object-fit:cover;flex-shrink:0;">` : `<div style="width:48px;height:48px;border-radius:0.5rem;background:rgba(124,108,255,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#7C6CFF" stroke-width="1.5"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg></div>`}
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:0.875rem;font-weight:600;color:#F0F4FF;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${item.title}</div>
                                <div style="font-size:0.75rem;color:#6B7280;margin-top:0.15rem;">${item.type}${item.meta ? ' · ' + item.meta : ''}</div>
                            </div>
                        </a>
                    `).join('');
                    results.style.display = 'block';
                })
                .catch(e => {
                    if (e.name !== 'AbortError') console.error(e);
                });
        }, 300);
    });

    // Close on click outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#nav-search-bar')) {
            results.style.display = 'none';
        }
    });

    // Close on escape
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            results.style.display = 'none';
            input.blur();
        }
    });
}

// ═══════════════════════════════════════════
// CUREVIA AI CHATBOT
// ═══════════════════════════════════════════
function initChatbot() {
    const fab       = document.getElementById('chatbot-toggle');
    const panel     = document.getElementById('chatbot-panel');
    const closeBtn  = document.getElementById('chatbot-close');
    const clearBtn  = document.getElementById('chatbot-clear');
    const msgArea   = document.getElementById('chatbot-messages');
    const input     = document.getElementById('chatbot-input');
    const sendBtn   = document.getElementById('chatbot-send');
    const iconOpen  = document.getElementById('chatbot-icon-open');
    const iconClose = document.getElementById('chatbot-icon-close');
    const chips     = document.getElementById('chatbot-suggestions');
    const statusEl  = document.getElementById('chatbot-status');

    if (!fab || !panel || !input || !sendBtn || !msgArea) return;

    let history = [];
    let isSending = false;
    let lastUserQuestion = '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // — Toggle panel
    function openPanel() {
        panel.classList.add('open');
        if (iconOpen) iconOpen.style.display = 'none';
        if (iconClose) iconClose.style.display = 'block';
        fab.setAttribute('aria-label', 'Close chatbot');
        input.focus();
    }
    function closePanel() {
        panel.classList.remove('open');
        if (iconOpen) iconOpen.style.display = 'block';
        if (iconClose) iconClose.style.display = 'none';
        fab.setAttribute('aria-label', 'Open Curevia AI chatbot');
    }

    fab.addEventListener('click', () => panel.classList.contains('open') ? closePanel() : openPanel());
    if (closeBtn) closeBtn.addEventListener('click', closePanel);

    // — Clear chat
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            history = [];
            lastUserQuestion = '';
            msgArea.innerHTML = '';
            addBotWelcome();
            if (chips) chips.style.display = 'flex';
        });
    }

    // — Auto-grow textarea
    input.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 120) + 'px';
        sendBtn.disabled = !input.value.trim();
    });

    // — Send on Enter (Shift+Enter for newline)
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (input.value.trim()) triggerSend();
        }
    });
    sendBtn.addEventListener('click', () => { if (input.value.trim()) triggerSend(); });

    // — Preset suggestion chips → redirect to full chat page
    if (chips) {
        chips.addEventListener('click', (e) => {
            const chip = e.target.closest('.chatbot-chip');
            if (!chip) return;
            const q = chip.getAttribute('data-q');
            if (q) window.location.href = '/chat?q=' + encodeURIComponent(q);
        });
    }

    // — Send message
    function triggerSend() {
        if (isSending) return;
        const message = input.value.trim();
        if (!message) return;

        lastUserQuestion = message;

        // Add user message to UI
        addMessage('user', message);
        history.push({ role: 'user', content: message });
        input.value = '';
        input.style.height = 'auto';
        sendBtn.disabled = true;

        // Hide preset chips, remove any previous AI chips
        if (chips) chips.style.display = 'none';
        msgArea.querySelectorAll('.chatbot-ai-chips-row').forEach(el => el.remove());

        // Show typing indicator
        const typingEl = addTyping();
        setStatus('thinking');
        isSending = true;

        fetch('/api/chatbot', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
            },
            body: JSON.stringify({
                message,
                model: localStorage.getItem('curevia_selected_model') || 'groq',
                history: history.slice(-20)
            }),
        })
        .then(res => res.json())
        .then(data => {
            typingEl.remove();
            const cleaned = cleanAiAnswer(
                data.answer || 'Sorry, I could not get a response.',
                data.suggestions || [],
                data.summary || null
            );
            const answer      = cleaned.answer;
            const summary     = cleaned.summary;
            const suggestions = cleaned.suggestions;

            const displayText = summary || answer;
            addMessage('assistant', displayText, true, answer);
            history.push({ role: 'assistant', content: answer });
            // AI-generated suggestion chips → redirect to full chat
            if (suggestions && suggestions.length > 0) {
                addMiniSuggestions(suggestions);
            }
            setStatus('online');
        })
        .catch(() => {
            typingEl.remove();
            addMessage('assistant', 'Curevia AI is taking a little nap. Try again in a moment! 🌙', true);
            setStatus('online');
        })
        .finally(() => { isSending = false; });
    }

    // — Add message to UI
    function addMessage(role, content, parseMarkdown = false, fullAnswer = null) {
        const wrap = document.createElement('div');
        wrap.className = `chatbot-msg ${role}`;

        const avatar = document.createElement('div');
        avatar.className = 'chatbot-msg-avatar';
        if (role === 'assistant') {
            avatar.innerHTML = '<svg width="14" height="14" viewBox="0 0 32 32" fill="none"><circle cx="16" cy="16" r="8" stroke="#22F2E2" stroke-width="1.5"/><circle cx="16" cy="16" r="3" fill="#22F2E2"/></svg>';
        } else {
            avatar.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="#0B0F14" stroke="#0B0F14" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
        }

        const bubble = document.createElement('div');
        bubble.className = `chatbot-bubble ${role}`;

        if (parseMarkdown && role === 'assistant') {
            bubble.innerHTML = renderMarkdown(content);
            // Add "Open full answer" link if a full detailed answer exists
            if (fullAnswer) {
                const linkRow = document.createElement('div');
                linkRow.className = 'chatbot-fulllink';
                const href = '/chat?q=' + encodeURIComponent(lastUserQuestion || content.slice(0, 80));
                linkRow.innerHTML = `<a href="${href}">Open full answer in Curevia AI &rarr;</a>`;
                bubble.appendChild(linkRow);
            }
        } else {
            bubble.textContent = content;
        }

        wrap.appendChild(avatar);
        wrap.appendChild(bubble);
        msgArea.appendChild(wrap);
        scrollToBottom();
    }

    // — AI-generated suggestion chips (redirect to full chat)
    function addMiniSuggestions(suggestions) {
        const row = document.createElement('div');
        row.className = 'chatbot-ai-chips-row';
        row.innerHTML = suggestions.map(s =>
            `<button class="chatbot-ai-chip" data-q="${s.replace(/"/g, '&quot;')}">${escapeHtml(s)}</button>`
        ).join('');
        row.addEventListener('click', (e) => {
            const chip = e.target.closest('.chatbot-ai-chip');
            if (!chip) return;
            const q = chip.getAttribute('data-q');
            if (q) window.location.href = '/chat?q=' + encodeURIComponent(q);
        });
        msgArea.appendChild(row);
        scrollToBottom();
    }

    // — Typing indicator
    function addTyping() {
        const wrap = document.createElement('div');
        wrap.className = 'chatbot-msg assistant';
        wrap.innerHTML = `
            <div class="chatbot-msg-avatar"><svg width="14" height="14" viewBox="0 0 32 32" fill="none"><circle cx="16" cy="16" r="8" stroke="#22F2E2" stroke-width="1.5"/><circle cx="16" cy="16" r="3" fill="#22F2E2"/></svg></div>
            <div class="chatbot-bubble assistant chatbot-typing">
                <div class="chatbot-typing-dot"></div>
                <div class="chatbot-typing-dot"></div>
                <div class="chatbot-typing-dot"></div>
            </div>`;
        msgArea.appendChild(wrap);
        scrollToBottom();
        return wrap;
    }

    // — Status
    function setStatus(state) {
        if (!statusEl) return;
        if (state === 'thinking') {
            statusEl.innerHTML = '<span style="width:5px;height:5px;border-radius:50%;background:#F59E0B;display:inline-block;animation:pulse-glow 1s infinite;"></span> Thinking...';
            statusEl.style.color = '#F59E0B';
        } else {
            statusEl.innerHTML = '<span style="width:5px;height:5px;border-radius:50%;background:#2DD4BF;display:inline-block;"></span> Online — Ask me anything';
            statusEl.style.color = '#2DD4BF';
        }
    }

    function scrollToBottom() {
        requestAnimationFrame(() => { msgArea.scrollTop = msgArea.scrollHeight; });
    }

    function addBotWelcome() {
        const html = `
            <div class="chatbot-msg assistant">
                <div class="chatbot-msg-avatar"><svg width="14" height="14" viewBox="0 0 32 32" fill="none"><circle cx="16" cy="16" r="8" stroke="#22F2E2" stroke-width="1.5"/><circle cx="16" cy="16" r="3" fill="#22F2E2"/></svg></div>
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
            </div>`;
        msgArea.insertAdjacentHTML('beforeend', html);
    }

    // — Simple Markdown → HTML renderer
    function renderMarkdown(text) {
        let html = text
            // Escape HTML entities for safety
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // Code blocks (```)
        html = html.replace(/```(\w*)\n([\s\S]*?)```/g, (_, lang, code) => {
            return `<pre><code class="language-${lang}">${code.trim()}</code></pre>`;
        });

        // Inline code
        html = html.replace(/`([^`]+)`/g, '<code>$1</code>');

        // Bold **text**
        html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');

        // Italic *text*
        html = html.replace(/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/g, '<em>$1</em>');

        // Headings
        html = html.replace(/^### (.+)$/gm, '<h3>$1</h3>');
        html = html.replace(/^## (.+)$/gm, '<h2>$1</h2>');
        html = html.replace(/^# (.+)$/gm, '<h1>$1</h1>');

        // Blockquote
        html = html.replace(/^&gt; (.+)$/gm, '<blockquote>$1</blockquote>');

        // Unordered lists
        html = html.replace(/^[\-\*] (.+)$/gm, '<li>$1</li>');
        html = html.replace(/((?:<li>.*<\/li>\n?)+)/g, '<ul>$1</ul>');

        // Ordered lists
        html = html.replace(/^\d+\. (.+)$/gm, '<li>$1</li>');

        // Links
        html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>');

        // Line breaks → paragraphs
        html = html.replace(/\n\n+/g, '</p><p>');
        html = html.replace(/\n/g, '<br>');
        html = '<p>' + html + '</p>';

        // Clean up empty paragraphs
        html = html.replace(/<p>\s*<\/p>/g, '');
        html = html.replace(/<p>(<h[1-3]>)/g, '$1');
        html = html.replace(/(<\/h[1-3]>)<\/p>/g, '$1');
        html = html.replace(/<p>(<pre>)/g, '$1');
        html = html.replace(/(<\/pre>)<\/p>/g, '$1');
        html = html.replace(/<p>(<ul>)/g, '$1');
        html = html.replace(/(<\/ul>)<\/p>/g, '$1');
        html = html.replace(/<p>(<blockquote>)/g, '$1');
        html = html.replace(/(<\/blockquote>)<\/p>/g, '$1');

        return html;
    }
}

// ═══════════════════════════════════════════
// FULL-PAGE CHATBOT (ChatGPT-like)
// ═══════════════════════════════════════════
function initFullPageChat() {
    const msgArea   = document.getElementById('chat-messages');
    const input     = document.getElementById('chat-input');
    const sendBtn   = document.getElementById('chat-send-btn');
    const welcome   = document.getElementById('chat-welcome');
    const anchor    = document.getElementById('chat-msgs-anchor');
    const sidebar   = document.getElementById('chat-sidebar');
    const overlay   = document.getElementById('chat-sidebar-overlay');
    const newBtn    = document.getElementById('chat-new-btn');
    const newMobile = document.getElementById('chat-new-mobile');
    const sideToggle= document.getElementById('chat-sidebar-toggle');
    const convList  = document.getElementById('chat-conversations');

    if (!msgArea || !input || !sendBtn) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let isSending = false;
    let currentSuggestionsEl = null; // tracks the active suggestion chips row

    // ── Model selection ──
    const MODEL_KEY = 'curevia_selected_model';
    let selectedModel = localStorage.getItem(MODEL_KEY) || 'gemini';

    const modelBtns   = document.querySelectorAll('.chat-model-btn');
    const modelLabel  = document.getElementById('chat-model-label');

    const MODEL_META = {
        gemini:       { label: 'Gemini 2.5 Flash' },
        groq:         { label: 'Groq · Llama 3.1 8B' },
        'groq-70b':   { label: 'Groq · Llama 3.3 70B' },
        llama4:       { label: 'Llama 4 Scout · 128k' },
        'gpt-oss-20b':{ label: 'GPT OSS 20B · 128k' },
    };

    function applyModelUI(model) {
        modelBtns.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.model === model);
        });
        if (modelLabel) modelLabel.textContent = MODEL_META[model]?.label || 'Curevia AI';
    }

    applyModelUI(selectedModel);

    modelBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            selectedModel = btn.dataset.model;
            localStorage.setItem(MODEL_KEY, selectedModel);
            applyModelUI(selectedModel);
        });
    });

    // ── Conversation storage ──
    const STORAGE_KEY = 'curevia_chats';
    let conversations = loadConversations();
    let activeConvId  = loadActiveConvId();

    function loadConversations() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
        } catch { return []; }
    }
    function saveConversations() {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(conversations));
        } catch { /* quota exceeded */ }
    }

    function loadActiveConvId() {
        const id = localStorage.getItem(STORAGE_KEY + '_active');
        if (id && conversations.some(c => c.id === id)) return id;
        return conversations.length > 0 ? conversations[0].id : null;
    }
    function saveActiveConvId() {
        if (activeConvId) {
            localStorage.setItem(STORAGE_KEY + '_active', activeConvId);
        } else {
            localStorage.removeItem(STORAGE_KEY + '_active');
        }
    }

    function createConversation() {
        const conv = {
            id: Date.now().toString(36) + Math.random().toString(36).slice(2, 6),
            title: 'New Chat',
            messages: [],
            createdAt: Date.now(),
        };
        conversations.unshift(conv);
        saveConversations();
        return conv;
    }

    function getActiveConv() {
        return conversations.find(c => c.id === activeConvId) || null;
    }

    // ── Render sidebar ──
    function renderSidebar() {
        if (!convList) return;
        if (conversations.length === 0) {
            convList.innerHTML = '<div style="padding:1.5rem 0.75rem;text-align:center;color:var(--text-muted);font-size:0.8rem;">No conversations yet</div>';
            return;
        }
        convList.innerHTML = conversations.map(c => `
            <button class="chat-conv-item ${c.id === activeConvId ? 'active' : ''}" data-id="${c.id}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="flex-shrink:0;opacity:0.5;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <span class="conv-title">${escapeHtml(c.title)}</span>
                <span class="conv-delete" data-del="${c.id}" title="Delete">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </span>
            </button>
        `).join('');
    }

    // ── Render messages for active conversation ──
    function renderMessages() {
        const conv = getActiveConv();
        // Clear existing message rows AND any suggestion chips
        msgArea.querySelectorAll('.chat-msg-row, .chat-typing-row, .chat-suggestions-row').forEach(el => el.remove());
        currentSuggestionsEl = null;

        if (!conv || conv.messages.length === 0) {
            if (welcome) welcome.style.display = 'flex';
            return;
        }
        if (welcome) welcome.style.display = 'none';

        conv.messages.forEach((msg, idx) => {
            appendMsgRow(msg.role, msg.content, false, idx);
        });
        scrollToBottom();
    }

    // ── Append related topic suggestion chips after a bot response ──
    function appendSuggestions(suggestions) {
        if (currentSuggestionsEl) {
            currentSuggestionsEl.remove();
            currentSuggestionsEl = null;
        }
        if (!suggestions || suggestions.length === 0) return;

        const row = document.createElement('div');
        row.className = 'chat-suggestions-row';
        row.innerHTML = `
            <div class="chat-suggestions-inner">
                <span class="chat-suggestions-label">Explore further</span>
                <div class="chat-suggestions-chips">
                    ${suggestions.map(s => `<button class="chat-suggestion-chip">${escapeHtml(s)}</button>`).join('')}
                </div>
            </div>
        `;
        msgArea.appendChild(row);
        currentSuggestionsEl = row;
        scrollToBottom();
    }

    // ── Append a single message row ──
    function appendMsgRow(role, content, animate, msgIdx) {
        if (welcome) welcome.style.display = 'none';

        const row = document.createElement('div');
        row.className = `chat-msg-row ${role === 'user' ? 'user-row' : 'assistant-row'}${animate ? ' animate-in' : ''}`;

        const isBot = role === 'assistant';
        const actionsHtml = isBot ? `
            <div class="chat-msg-actions">
                <button class="chat-msg-actions-btn" aria-label="Message options">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                </button>
                <div class="chat-msg-dropdown">
                    <button class="chat-msg-dropdown-item" data-action="copy">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        Copy text
                    </button>
                    <div class="chat-msg-dropdown-sep"></div>
                    <button class="chat-msg-dropdown-item" data-action="share-story">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 20h9"/><path d="M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z"/></svg>
                        Share as Story
                    </button>
                    <button class="chat-msg-dropdown-item" data-action="share-encyclopedia">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"/></svg>
                        Share as Encyclopedia
                    </button>
                </div>
            </div>
        ` : '';

        row.innerHTML = `
            <div class="chat-msg-content">
                <div class="chat-msg-icon ${isBot ? 'bot-icon' : 'user-icon'}">
                    ${isBot
                        ? '<svg width="16" height="16" viewBox="0 0 32 32" fill="none"><circle cx="16" cy="16" r="8" stroke="#22F2E2" stroke-width="1.5"/><circle cx="16" cy="16" r="3" fill="#22F2E2"/></svg>'
                        : '<svg width="14" height="14" viewBox="0 0 24 24" fill="#0B0F14" stroke="#0B0F14" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>'
                    }
                </div>
                <div class="chat-msg-body">
                    <div class="chat-msg-role ${isBot ? 'bot' : 'user'}">${isBot ? 'Curevia AI' : 'You'}</div>
                    <div class="chat-msg-text ${isBot ? '' : 'user-text'}">${isBot ? chatRenderMarkdown(content) : escapeHtml(content)}</div>
                </div>
                ${actionsHtml}
            </div>
        `;

        // Store raw content for sharing
        if (isBot) row.dataset.rawContent = content;

        msgArea.appendChild(row);
        return row;
    }

    // ── Close all open dropdowns ──
    function closeAllDropdowns() {
        document.querySelectorAll('.chat-msg-dropdown.open').forEach(d => d.classList.remove('open'));
    }

    // ── Dropdown & action delegation on message area ──
    msgArea.addEventListener('click', (e) => {
        // Toggle dropdown
        const actBtn = e.target.closest('.chat-msg-actions-btn');
        if (actBtn) {
            e.stopPropagation();
            const dd = actBtn.nextElementSibling;
            const wasOpen = dd.classList.contains('open');
            closeAllDropdowns();
            if (!wasOpen) dd.classList.add('open');
            return;
        }

        // Dropdown actions
        const ddItem = e.target.closest('.chat-msg-dropdown-item');
        if (ddItem) {
            e.stopPropagation();
            closeAllDropdowns();
            const action = ddItem.dataset.action;
            const msgRow = ddItem.closest('.chat-msg-row');
            const rawContent = msgRow?.dataset.rawContent || '';
            const textEl = msgRow?.querySelector('.chat-msg-text');

            if (action === 'copy') {
                navigator.clipboard.writeText(rawContent || textEl?.textContent || '').then(() => {
                    ddItem.textContent = 'Copied!';
                    setTimeout(() => { ddItem.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>Copy text`; }, 1200);
                });
            }
            if (action === 'share-story' || action === 'share-encyclopedia') {
                openShareModal(action === 'share-encyclopedia' ? 'encyclopedia' : 'story', rawContent);
            }
            return;
        }

        // Suggestion chip click → send as new message
        const chip = e.target.closest('.chat-suggestion-chip');
        if (chip) {
            const text = chip.textContent.trim();
            if (text) {
                input.value = text;
                triggerSend();
            }
            return;
        }
    });

    // Close dropdowns on outside click
    document.addEventListener('click', () => closeAllDropdowns());

    // ── Share Modal ──
    const shareOverlay  = document.getElementById('share-modal-overlay');
    const shareClose    = document.getElementById('share-modal-close');
    const shareCancel   = document.getElementById('share-cancel');
    const sharePublish  = document.getElementById('share-publish');
    const sharePubText  = document.getElementById('share-publish-text');
    const shareTitle    = document.getElementById('share-title');
    const sharePreview  = document.getElementById('share-preview');
    const shareStatus   = document.getElementById('share-status');
    const shareTypeBtns = document.querySelectorAll('.share-type-btn');
    const shareCategory = document.getElementById('share-category');
    const shareImage    = document.getElementById('share-image');
    const shareDesc     = document.getElementById('share-description');
    const shareQfSec    = document.getElementById('share-qf-section');
    const shareQfRows   = document.getElementById('share-qf-rows');
    const shareQfAdd    = document.getElementById('share-qf-add');
    let shareType = 'story';
    let shareRawContent = '';

    function addQfRow(label = '', value = '') {
        const row = document.createElement('div');
        row.className = 'share-qf-row';
        row.innerHTML = `
            <input class="qf-label" type="text" placeholder="Label (e.g. Diameter)" value="${label.replace(/"/g,'&quot;')}">
            <input class="qf-value" type="text" placeholder="Value (e.g. 1.4 million km)" value="${value.replace(/"/g,'&quot;')}">
            <button class="share-qf-remove" type="button" title="Remove">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        `;
        row.querySelector('.share-qf-remove').addEventListener('click', () => row.remove());
        if (shareQfRows) shareQfRows.appendChild(row);
    }

    if (shareQfAdd) shareQfAdd.addEventListener('click', () => addQfRow());

    function openShareModal(type, content) {
        shareType = type;
        shareRawContent = content;

        // Set active type button and show/hide quick facts
        shareTypeBtns.forEach(b => b.classList.toggle('active', b.dataset.type === type));
        if (shareQfSec) shareQfSec.style.display = type === 'encyclopedia' ? 'flex' : 'none';
        if (shareQfRows) shareQfRows.innerHTML = '';

        // Guess title from first heading or first line
        const firstHeading = content.match(/^#+\s+(.+)$/m);
        const firstLine = content.split('\n')[0]?.replace(/[#*_`]/g, '').trim();
        shareTitle.value = firstHeading ? firstHeading[1] : (firstLine?.length > 5 ? firstLine.slice(0, 100) : '');

        // Reset fields
        if (shareCategory) shareCategory.value = '';
        if (shareImage) shareImage.value = '';
        if (shareDesc) shareDesc.value = '';

        // Preview
        sharePreview.innerHTML = chatRenderMarkdown(content.slice(0, 800) + (content.length > 800 ? '\n\n...' : ''));

        shareStatus.textContent = '';
        shareStatus.className = 'share-status';
        sharePublish.disabled = false;
        sharePubText.textContent = 'Publish';

        shareOverlay.classList.add('open');
    }

    function closeShareModal() {
        shareOverlay.classList.remove('open');
    }

    if (shareClose) shareClose.addEventListener('click', closeShareModal);
    if (shareCancel) shareCancel.addEventListener('click', closeShareModal);
    if (shareOverlay) shareOverlay.addEventListener('click', (e) => {
        if (e.target === shareOverlay) closeShareModal();
    });

    // Type toggle
    shareTypeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            shareType = btn.dataset.type;
            shareTypeBtns.forEach(b => b.classList.toggle('active', b.dataset.type === shareType));
            if (shareQfSec) shareQfSec.style.display = shareType === 'encyclopedia' ? 'flex' : 'none';
        });
    });

    // Publish
    if (sharePublish) sharePublish.addEventListener('click', async () => {
        const title = shareTitle.value.trim();
        if (!title) {
            shareStatus.textContent = 'Please enter a title.';
            shareStatus.className = 'share-status error';
            return;
        }

        // Collect quick facts
        const quickFacts = [];
        if (shareQfRows) {
            shareQfRows.querySelectorAll('.share-qf-row').forEach(row => {
                const lbl = row.querySelector('.qf-label')?.value.trim();
                const val = row.querySelector('.qf-value')?.value.trim();
                if (lbl && val) quickFacts.push({ label: lbl, value: val });
            });
        }

        sharePublish.disabled = true;
        sharePubText.textContent = 'Publishing...';
        shareStatus.textContent = '';
        shareStatus.className = 'share-status';

        try {
            const res = await fetch('/api/chatbot/share', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify({
                    type: shareType,
                    title: title,
                    content: shareRawContent,
                    category: shareCategory?.value || '',
                    image_url: shareImage?.value.trim() || '',
                    description: shareDesc?.value.trim() || '',
                    quick_facts: quickFacts,
                }),
            });

            const data = await res.json();

            if (data.success) {
                shareStatus.textContent = data.message;
                shareStatus.className = 'share-status success';
                sharePubText.textContent = 'Published!';
                setTimeout(() => {
                    closeShareModal();
                    if (data.url) window.open(data.url, '_blank');
                }, 1200);
            } else {
                shareStatus.textContent = data.message || 'Failed to publish.';
                shareStatus.className = 'share-status error';
                sharePublish.disabled = false;
                sharePubText.textContent = 'Publish';
            }
        } catch {
            shareStatus.textContent = 'Network error. Please try again.';
            shareStatus.className = 'share-status error';
            sharePublish.disabled = false;
            sharePubText.textContent = 'Publish';
        }
    });

    // ── Typing indicator ──
    function addTypingRow() {
        const row = document.createElement('div');
        row.className = 'chat-typing-row';
        row.id = 'fp-typing';
        row.innerHTML = `
            <div class="chat-msg-content">
                <div class="chat-msg-icon bot-icon">
                    <svg width="16" height="16" viewBox="0 0 32 32" fill="none"><circle cx="16" cy="16" r="8" stroke="#22F2E2" stroke-width="1.5"/><circle cx="16" cy="16" r="3" fill="#22F2E2"/></svg>
                </div>
                <div class="chat-msg-body">
                    <div class="chat-msg-role bot">Curevia AI</div>
                    <div class="chat-typing-dots"><span></span><span></span><span></span></div>
                </div>
            </div>
        `;
        msgArea.appendChild(row);
        scrollToBottom();
        return row;
    }

    // ── Auto-grow textarea ──
    input.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 200) + 'px';
        sendBtn.disabled = !input.value.trim();
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (input.value.trim()) triggerSend();
        }
    });
    sendBtn.addEventListener('click', () => { if (input.value.trim()) triggerSend(); });

    // ── Send message ──
    function triggerSend() {
        if (isSending) return;
        const message = input.value.trim();
        if (!message) return;

        if (!activeConvId) {
            const conv = createConversation();
            activeConvId = conv.id;
            saveActiveConvId();
        }

        const conv = getActiveConv();
        if (!conv) return;

        conv.messages.push({ role: 'user', content: message });

        if (conv.title === 'New Chat') {
            conv.title = message.length > 50 ? message.slice(0, 47) + '...' : message;
        }
        saveConversations();
        renderSidebar();

        const msgIdx = conv.messages.length - 1;
        appendMsgRow('user', message, true, msgIdx);
        input.value = '';
        input.style.height = 'auto';
        sendBtn.disabled = true;
        scrollToBottom();

        const typingRow = addTypingRow();
        isSending = true;

        fetch('/api/chatbot', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
            },
            body: JSON.stringify({
                message,
                model: selectedModel,
                history: conv.messages.filter(m => m.role === 'user' || m.role === 'assistant').slice(-20)
            }),
        })
        .then(res => res.json())
        .then(data => {
            typingRow.remove();
            const cleaned = cleanAiAnswer(
                data.answer || 'Sorry, I could not get a response.',
                data.suggestions || [],
                data.summary || null
            );
            const answer      = cleaned.answer;
            const suggestions = cleaned.suggestions;

            conv.messages.push({ role: 'assistant', content: answer });
            saveConversations();
            const aidx = conv.messages.length - 1;
            const botRow = appendMsgRow('assistant', answer, true, aidx);
            // Scroll to TOP of new bot message so user reads from beginning
            scrollToMsgTop(botRow);
            // Show related topic suggestions
            if (suggestions.length > 0) {
                appendSuggestions(suggestions);
            }
        })
        .catch(() => {
            typingRow.remove();
            const err = 'Sorry, something went wrong. Please check your connection and try again.';
            conv.messages.push({ role: 'assistant', content: err });
            saveConversations();
            const eidx = conv.messages.length - 1;
            appendMsgRow('assistant', err, true, eidx);
            scrollToBottom();
        })
        .finally(() => { isSending = false; });
    }

    // ── Welcome card clicks ──
    if (welcome) {
        welcome.addEventListener('click', (e) => {
            const card = e.target.closest('.chat-welcome-card');
            if (!card) return;
            const q = card.getAttribute('data-q');
            if (q) { input.value = q; triggerSend(); }
        });
    }

    // ── New chat ──
    function startNewChat() {
        activeConvId = null;
        saveActiveConvId();
        renderMessages();
        renderSidebar();
        input.value = '';
        input.style.height = 'auto';
        sendBtn.disabled = true;
        input.focus();
        closeSidebar();
    }
    if (newBtn) newBtn.addEventListener('click', startNewChat);
    if (newMobile) newMobile.addEventListener('click', startNewChat);

    // ── Switch conversation ──
    if (convList) {
        convList.addEventListener('click', (e) => {
            const delBtn = e.target.closest('.conv-delete');
            if (delBtn) {
                const delId = delBtn.getAttribute('data-del');
                conversations = conversations.filter(c => c.id !== delId);
                saveConversations();
                if (activeConvId === delId) {
                    activeConvId = null;
                    saveActiveConvId();
                    renderMessages();
                }
                renderSidebar();
                return;
            }
            const item = e.target.closest('.chat-conv-item');
            if (item) {
                activeConvId = item.getAttribute('data-id');
                saveActiveConvId();
                renderMessages();
                renderSidebar();
                scrollToBottom();
                closeSidebar();
            }
        });
    }

    // ── Mobile sidebar toggle ──
    function openSidebar() {
        if (sidebar) sidebar.classList.add('open');
        if (overlay) overlay.classList.add('open');
    }
    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('open');
    }
    if (sideToggle) sideToggle.addEventListener('click', openSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);

    // ── Utilities ──
    function scrollToBottom() {
        requestAnimationFrame(() => { msgArea.scrollTop = msgArea.scrollHeight; });
    }

    function scrollToMsgTop(el) {
        requestAnimationFrame(() => {
            const top = el.offsetTop - msgArea.offsetTop;
            msgArea.scrollTo({ top: Math.max(0, top - 12), behavior: 'smooth' });
        });
    }

    function chatRenderMarkdown(text) {
        let html = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        html = html.replace(/```(\w*)\n([\s\S]*?)```/g, (_, lang, code) =>
            `<pre><code class="language-${lang}">${code.trim()}</code></pre>`);
        html = html.replace(/`([^`]+)`/g, '<code>$1</code>');
        html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/g, '<em>$1</em>');
        html = html.replace(/^### (.+)$/gm, '<h3>$1</h3>');
        html = html.replace(/^## (.+)$/gm, '<h2>$1</h2>');
        html = html.replace(/^# (.+)$/gm, '<h1>$1</h1>');
        html = html.replace(/^&gt; (.+)$/gm, '<blockquote>$1</blockquote>');
        html = html.replace(/^[\-\*] (.+)$/gm, '<li>$1</li>');
        html = html.replace(/((?:<li>.*<\/li>\n?)+)/g, '<ul>$1</ul>');
        html = html.replace(/^\d+\. (.+)$/gm, '<li>$1</li>');
        html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>');
        html = html.replace(/\n\n+/g, '</p><p>');
        html = html.replace(/\n/g, '<br>');
        html = '<p>' + html + '</p>';
        html = html.replace(/<p>\s*<\/p>/g, '');
        html = html.replace(/<p>(<h[1-3]>)/g, '$1');
        html = html.replace(/(<\/h[1-3]>)<\/p>/g, '$1');
        html = html.replace(/<p>(<pre>)/g, '$1');
        html = html.replace(/(<\/pre>)<\/p>/g, '$1');
        html = html.replace(/<p>(<ul>)/g, '$1');
        html = html.replace(/(<\/ul>)<\/p>/g, '$1');
        html = html.replace(/<p>(<blockquote>)/g, '$1');
        html = html.replace(/(<\/blockquote>)<\/p>/g, '$1');

        // Add copy buttons to code blocks
        html = html.replace(/<pre><code([^>]*)>(.*?)<\/code><\/pre>/gs, (_, attrs, code) => {
            return `<pre><button class="chat-code-copy" onclick="navigator.clipboard.writeText(this.nextElementSibling.textContent).then(()=>{this.textContent='Copied!';setTimeout(()=>{this.textContent='Copy'},1500)})">Copy</button><code${attrs}>${code}</code></pre>`;
        });

        return html;
    }

    // ── Init ──
    renderSidebar();
    renderMessages();
    input.focus();

    // ── Auto-send question from ?q= URL param (e.g. redirected from mini chatbot) ──
    const urlQ = new URLSearchParams(window.location.search).get('q');
    if (urlQ) {
        // Clean the URL without reloading
        window.history.replaceState({}, '', window.location.pathname);
        input.value = urlQ;
        sendBtn.disabled = false;
        // Small delay so the page renders first
        setTimeout(() => triggerSend(), 350);
    }
}
