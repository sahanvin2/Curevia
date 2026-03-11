@extends('layouts.app')

@section('title', 'Contact Us — Curevia')
@section('meta_description', "Get in touch with the Curevia team. We'd love to hear from you — questions, feedback, partnerships, or just to say hello.")

@section('content')
<section style="padding:7rem 0 5rem;">
<div style="max-width:960px;margin:0 auto;padding:0 1.5rem;">

    {{-- Hero --}}
    <div style="text-align:center;margin-bottom:4rem;">
        <div style="display:inline-flex;align-items:center;gap:0.5rem;background:rgba(34,242,226,0.06);border:1px solid rgba(34,242,226,0.18);border-radius:100px;padding:0.4rem 1.25rem;font-size:0.78rem;font-weight:700;color:var(--accent-cyan);text-transform:uppercase;letter-spacing:.12em;margin-bottom:1.5rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            Contact
        </div>
        <h1 style="font-size:clamp(2.2rem,5vw,3.5rem);font-weight:900;color:var(--text-primary);line-height:1.1;letter-spacing:-0.03em;margin-bottom:1rem;">We'd love to <span style="background:linear-gradient(135deg,var(--accent-cyan),var(--accent-violet));-webkit-background-clip:text;-webkit-text-fill-color:transparent;">hear from you</span></h1>
        <p style="font-size:1.05rem;color:var(--text-secondary);line-height:1.8;max-width:540px;margin:0 auto;">Have a question, suggestion, or just want to say hello? Drop us a message and our team will get back to you within 24 hours.</p>
    </div>

    <div style="display:grid;grid-template-columns:1fr 340px;gap:2.5rem;align-items:start;">

        {{-- Contact Form --}}
        <div>
            @if(session('success'))
            <div style="background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.3);border-radius:1rem;padding:1.25rem 1.5rem;margin-bottom:1.75rem;display:flex;align-items:center;gap:0.75rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span style="font-size:0.9rem;color:#22C55E;font-weight:600;">{{ session('success') }}</span>
            </div>
            @endif

            <form method="POST" action="{{ route('contact') }}" style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.5rem;padding:2.25rem;">
                @csrf

                {{-- Name --}}
                <div style="margin-bottom:1.25rem;">
                    <label style="display:block;font-size:0.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:0.5rem;">Your Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="John Doe"
                        style="width:100%;background:rgba(11,15,20,0.8);border:1px solid var(--border-subtle);border-radius:0.75rem;padding:0.8rem 1rem;color:var(--text-primary);font-size:0.9rem;outline:none;box-sizing:border-box;transition:border-color .2s;"
                        onfocus="this.style.borderColor='var(--accent-cyan)'" onblur="this.style.borderColor='var(--border-subtle)'">
                    @error('name')<p style="font-size:0.78rem;color:#F87171;margin-top:0.35rem;">{{ $message }}</p>@enderror
                </div>

                {{-- Email --}}
                <div style="margin-bottom:1.25rem;">
                    <label style="display:block;font-size:0.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:0.5rem;">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="you@example.com"
                        style="width:100%;background:rgba(11,15,20,0.8);border:1px solid var(--border-subtle);border-radius:0.75rem;padding:0.8rem 1rem;color:var(--text-primary);font-size:0.9rem;outline:none;box-sizing:border-box;transition:border-color .2s;"
                        onfocus="this.style.borderColor='var(--accent-cyan)'" onblur="this.style.borderColor='var(--border-subtle)'">
                    @error('email')<p style="font-size:0.78rem;color:#F87171;margin-top:0.35rem;">{{ $message }}</p>@enderror
                </div>

                {{-- Subject --}}
                <div style="margin-bottom:1.25rem;">
                    <label style="display:block;font-size:0.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:0.5rem;">Subject *</label>
                    <select name="subject" required
                        style="width:100%;background:rgba(11,15,20,0.8);border:1px solid var(--border-subtle);border-radius:0.75rem;padding:0.8rem 1rem;color:var(--text-primary);font-size:0.9rem;outline:none;box-sizing:border-box;appearance:none;cursor:pointer;">
                        @foreach([
                            'general'       => 'General Inquiry',
                            'editorial'     => 'Editorial / Content Feedback',
                            'technical'     => 'Technical Issue',
                            'advertising'   => 'Advertising & Partnerships',
                            'contributor'   => 'Become a Contributor',
                            'press'         => 'Press & Media',
                            'other'         => 'Other',
                        ] as $val => $label)
                        <option value="{{ $val }}" {{ old('subject', request('subject')) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('subject')<p style="font-size:0.78rem;color:#F87171;margin-top:0.35rem;">{{ $message }}</p>@enderror
                </div>

                {{-- Message --}}
                <div style="margin-bottom:1.75rem;">
                    <label style="display:block;font-size:0.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:0.5rem;">Message *</label>
                    <textarea name="message" required rows="6" placeholder="Tell us what's on your mind..."
                        style="width:100%;background:rgba(11,15,20,0.8);border:1px solid var(--border-subtle);border-radius:0.75rem;padding:0.8rem 1rem;color:var(--text-primary);font-size:0.9rem;outline:none;box-sizing:border-box;resize:vertical;font-family:inherit;transition:border-color .2s;"
                        onfocus="this.style.borderColor='var(--accent-cyan)'" onblur="this.style.borderColor='var(--border-subtle)'"
                    >{{ old('message') }}</textarea>
                    @error('message')<p style="font-size:0.78rem;color:#F87171;margin-top:0.35rem;">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:0.9rem;font-size:0.95rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:0.5rem"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    Send Message
                </button>
            </form>
        </div>

        {{-- Contact Info Sidebar --}}
        <div style="display:flex;flex-direction:column;gap:1.25rem;">

            {{-- Direct contact --}}
            <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;padding:1.5rem;">
                <h3 style="font-size:0.85rem;font-weight:800;color:var(--text-primary);text-transform:uppercase;letter-spacing:.07em;margin-bottom:1.25rem;">Direct Contact</h3>
                @foreach([
                    ['M','General','hello@curevia.com','mailto:hello@curevia.com'],
                    ['M','Editorial','editorial@curevia.com','mailto:editorial@curevia.com'],
                    ['M','Advertising','ads@curevia.com','mailto:ads@curevia.com'],
                ] as [$icon,$label,$val,$href])
                <div style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.6rem 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border-subtle);' : '' }}">
                    <div style="width:30px;height:30px;border-radius:8px;background:rgba(34,242,226,0.07);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--accent-cyan)" stroke-width="2"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </div>
                    <div>
                        <div style="font-size:0.72rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.06em;">{{ $label }}</div>
                        <a href="{{ $href }}" style="font-size:0.83rem;color:var(--accent-cyan);text-decoration:none;">{{ $val }}</a>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Response time --}}
            <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;padding:1.5rem;">
                <h3 style="font-size:0.85rem;font-weight:800;color:var(--text-primary);text-transform:uppercase;letter-spacing:.07em;margin-bottom:1.25rem;">Response Times</h3>
                @foreach([
                    ['General','Within 24 hours'],
                    ['Editorial','1–2 business days'],
                    ['Advertising','Same business day'],
                    ['Technical','Within 4 hours'],
                ] as [$type,$time])
                <div style="display:flex;justify-content:space-between;align-items:center;padding:0.5rem 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border-subtle);' : '' }}">
                    <span style="font-size:0.82rem;color:var(--text-secondary);">{{ $type }}</span>
                    <span style="font-size:0.78rem;font-weight:700;color:var(--accent-cyan);background:rgba(34,242,226,0.06);padding:0.2rem 0.6rem;border-radius:100px;">{{ $time }}</span>
                </div>
                @endforeach
            </div>

            {{-- Location --}}
            <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;padding:1.5rem;">
                <h3 style="font-size:0.85rem;font-weight:800;color:var(--text-primary);text-transform:uppercase;letter-spacing:.07em;margin-bottom:1rem;">Based in</h3>
                <div style="display:flex;gap:0.75rem;align-items:flex-start;">
                    <div style="width:30px;height:30px;border-radius:8px;background:rgba(124,108,255,0.08);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--accent-violet)" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div>
                        <div style="font-size:0.88rem;color:var(--text-primary);font-weight:600;">Internet, Worldwide</div>
                        <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.2rem;">A global digital platform serving curious minds everywhere</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
</section>
@endsection
