{{-- ═══════ GLOBAL SHARE MODAL ═══════ --}}
{{-- Call: openShareModal('Page Title', 'https://...', 'story|article|product') --}}

<div id="shareModal" style="position:fixed;inset:0;z-index:10500;align-items:flex-end;justify-content:center;padding:0;display:none;" role="dialog" aria-modal="true" aria-label="Share">
    <div id="shareBackdrop" onclick="cureviaShare.close()" style="position:absolute;inset:0;background:rgba(0,0,0,0.65);backdrop-filter:blur(6px);"></div>

    <div id="sharePanel" style="position:relative;width:100%;max-width:520px;background:rgba(13,17,27,0.98);border:1px solid rgba(34,242,226,0.15);border-top:2px solid rgba(34,242,226,0.3);border-radius:1.5rem 1.5rem 0 0;padding:1.5rem 1.5rem 2.5rem;transform:translateY(100%);transition:transform .4s cubic-bezier(.32,1,.23,1);box-shadow:0 -20px 60px rgba(0,0,0,0.6);">

        {{-- Drag handle --}}
        <div style="width:40px;height:4px;background:rgba(255,255,255,0.15);border-radius:100px;margin:0 auto 1.5rem;"></div>

        {{-- Header --}}
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;gap:1rem;">
            <div style="min-width:0;">
                <h3 id="share-modal-label" style="font-size:1.05rem;font-weight:800;color:var(--text-primary);margin:0 0 0.25rem;letter-spacing:-0.01em;">Share</h3>
                <p id="share-url-preview" style="font-size:0.73rem;color:var(--text-muted);margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:340px;"></p>
            </div>
            <button onclick="cureviaShare.close()" aria-label="Close share panel"
                style="width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:var(--text-muted);display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;transition:background .2s;"
                onmouseover="this.style.background='rgba(255,255,255,0.13)'" onmouseout="this.style.background='rgba(255,255,255,0.06)'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        {{-- Copy Link Bar --}}
        <div style="display:flex;align-items:center;gap:0.5rem;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.09);border-radius:0.85rem;padding:0.5rem 0.5rem 0.5rem 1rem;margin-bottom:1.5rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="1.5" style="flex-shrink:0;">
                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
            </svg>
            <span id="share-link-text" style="flex:1;font-size:0.78rem;color:var(--text-secondary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></span>
            <button id="share-copy-btn" onclick="cureviaShare.copyLink()"
                style="padding:0.5rem 1.1rem;background:linear-gradient(135deg,var(--accent-cyan),var(--accent-violet));border:none;border-radius:0.6rem;font-size:0.8rem;font-weight:700;color:#0b0f14;cursor:pointer;flex-shrink:0;transition:all .2s;display:flex;align-items:center;gap:0.35rem;">
                <svg id="share-copy-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <rect x="9" y="9" width="13" height="13" rx="2"/>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                </svg>
                <span id="share-copy-text">Copy</span>
            </button>
        </div>

        {{-- Platform Grid --}}
        <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:0.65rem;margin-bottom:0.75rem;">

            <button onclick="cureviaShare.toTwitter()" class="curevia-share-btn" data-hbg="rgba(0,0,0,0.6)" data-hborder="rgba(255,255,255,0.3)" title="Share on Twitter / X">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="#e7e9ea">
                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.746l7.73-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                </svg>
                <span>Twitter</span>
            </button>

            <button onclick="cureviaShare.toWhatsApp()" class="curevia-share-btn" data-hbg="rgba(37,211,102,0.12)" data-hborder="rgba(37,211,102,0.4)" title="Share on WhatsApp">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="#25D366">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                <span>WhatsApp</span>
            </button>

            <button onclick="cureviaShare.toFacebook()" class="curevia-share-btn" data-hbg="rgba(24,119,242,0.12)" data-hborder="rgba(24,119,242,0.4)" title="Share on Facebook">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="#1877F2">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                <span>Facebook</span>
            </button>

            <button onclick="cureviaShare.toLinkedIn()" class="curevia-share-btn" data-hbg="rgba(0,119,181,0.12)" data-hborder="rgba(0,119,181,0.4)" title="Share on LinkedIn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="#0077B5">
                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                </svg>
                <span>LinkedIn</span>
            </button>

            <button onclick="cureviaShare.toTelegram()" class="curevia-share-btn" data-hbg="rgba(41,182,246,0.12)" data-hborder="rgba(41,182,246,0.4)" title="Share on Telegram">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="#29B6F6">
                    <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                </svg>
                <span>Telegram</span>
            </button>

        </div>

        {{-- Email --}}
        <button onclick="cureviaShare.byEmail()"
            style="display:flex;width:100%;align-items:center;gap:0.75rem;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:0.85rem;padding:0.75rem 1.1rem;cursor:pointer;color:var(--text-secondary);transition:background .2s;box-sizing:border-box;"
            onmouseover="this.style.background='rgba(255,255,255,0.07)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'">
            <div style="width:36px;height:36px;border-radius:50%;background:rgba(34,242,226,0.08);border:1px solid rgba(34,242,226,0.18);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent-cyan)" stroke-width="1.5">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                </svg>
            </div>
            <div style="text-align:left;">
                <div style="font-size:0.85rem;font-weight:700;color:var(--text-primary);">Share via Email</div>
                <div style="font-size:0.71rem;color:var(--text-muted);">Send link to someone</div>
            </div>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2" style="margin-left:auto;flex-shrink:0;"><path d="M9 18l6-6-6-6"/></svg>
        </button>

    </div>
</div>

<style>
.curevia-share-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.4rem;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 1rem;
    padding: 0.85rem 0.3rem 0.65rem;
    cursor: pointer;
    transition: transform .2s, background .2s, border-color .2s;
}
.curevia-share-btn span {
    font-size: 0.64rem;
    font-weight: 600;
    color: var(--text-muted);
    letter-spacing: 0.01em;
}
.curevia-share-btn:hover { transform: translateY(-3px); }
</style>

<script>
(function () {
    var _t = '', _u = '';

    window.openShareModal = function (title, url, label) {
        _t = title || document.title;
        _u = url  || location.href;
        var lbl = label ? 'Share this ' + label : 'Share';
        document.getElementById('share-modal-label').textContent = lbl;
        document.getElementById('share-url-preview').textContent = _u;
        document.getElementById('share-link-text').textContent   = _u;
        // reset copy btn
        document.getElementById('share-copy-text').textContent = 'Copy';
        document.getElementById('share-copy-icon').innerHTML =
            '<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>';
        document.getElementById('share-copy-btn').style.background = 'linear-gradient(135deg,var(--accent-cyan),var(--accent-violet))';
        var modal = document.getElementById('shareModal');
        modal.style.display = 'flex';
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                document.getElementById('sharePanel').style.transform = 'translateY(0)';
            });
        });
        document.body.style.overflow = 'hidden';
    };

    window.cureviaShare = {
        close: function () {
            document.getElementById('sharePanel').style.transform = 'translateY(100%)';
            setTimeout(function () {
                document.getElementById('shareModal').style.display = 'none';
                document.body.style.overflow = '';
            }, 420);
        },
        copyLink: function () {
            var btn  = document.getElementById('share-copy-btn');
            var icon = document.getElementById('share-copy-icon');
            var txt  = document.getElementById('share-copy-text');
            function doConfirm() {
                btn.style.background = 'linear-gradient(135deg,#34D399,#059669)';
                icon.innerHTML = '<path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>';
                txt.textContent = 'Copied!';
                setTimeout(function () {
                    btn.style.background = 'linear-gradient(135deg,var(--accent-cyan),var(--accent-violet))';
                    icon.innerHTML = '<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>';
                    txt.textContent = 'Copy';
                }, 2200);
            }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(_u).then(doConfirm).catch(function () {
                    fallbackCopy(_u); doConfirm();
                });
            } else {
                fallbackCopy(_u); doConfirm();
            }
        },
        toTwitter:  function () { window.open('https://twitter.com/intent/tweet?text=' + encodeURIComponent(_t) + '&url=' + encodeURIComponent(_u), '_blank', 'width=600,height=400'); },
        toWhatsApp: function () { window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(_t + ' ' + _u), '_blank'); },
        toFacebook: function () { window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(_u), '_blank', 'width=620,height=420'); },
        toLinkedIn: function () { window.open('https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(_u), '_blank', 'width=620,height=520'); },
        toTelegram: function () { window.open('https://t.me/share/url?url=' + encodeURIComponent(_u) + '&text=' + encodeURIComponent(_t), '_blank'); },
        byEmail:    function () { window.location.href = 'mailto:?subject=' + encodeURIComponent(_t) + '&body=' + encodeURIComponent('Check this out:\n' + _u); }
    };

    function fallbackCopy(text) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.cssText = 'position:fixed;opacity:0;top:0;left:0;';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta);
    }

    // Hover effects
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.curevia-share-btn').forEach(function (btn) {
            var hbg = btn.dataset.hbg, hb = btn.dataset.hborder;
            btn.addEventListener('mouseenter', function () {
                if (hbg) btn.style.background   = hbg;
                if (hb)  btn.style.borderColor  = hb;
            });
            btn.addEventListener('mouseleave', function () {
                btn.style.background  = 'rgba(255,255,255,0.04)';
                btn.style.borderColor = 'rgba(255,255,255,0.08)';
            });
        });
    });

    // Close on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            var m = document.getElementById('shareModal');
            if (m && m.style.display !== 'none') cureviaShare.close();
        }
    });
}());
</script>
