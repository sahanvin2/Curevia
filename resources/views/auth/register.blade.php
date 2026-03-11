@extends('layouts.app')

@section('title', 'Join Curevia — Create Account')

@section('content')
<section style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:6rem 1.5rem 3rem;">
    <div style="width:100%;max-width:480px;">
        <div class="glass-card" style="padding:2.5rem;border-radius:1.5rem;">
            <div style="text-align:center;margin-bottom:2rem;">
                <a href="{{ route('home') }}" class="logo-text" style="font-size:1.75rem;text-decoration:none;display:block;margin-bottom:0.75rem;">Curevia</a>
                <p style="color:var(--text-secondary);font-size:0.9rem;">Begin your journey across the ocean of knowledge.</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div style="margin-bottom:1.25rem;">
                    <label style="display:block;font-size:0.8rem;font-weight:600;color:var(--text-secondary);margin-bottom:0.5rem;text-transform:uppercase;letter-spacing:0.05em;">Full Name</label>
                    <input type="text" name="name" class="input-field" placeholder="Your full name" value="{{ old('name') }}" required autofocus>
                    @error('name')
                    <span style="font-size:0.75rem;color:#EF4444;margin-top:0.25rem;display:block;">{{ $message }}</span>
                    @enderror
                </div>

                <div style="margin-bottom:1.25rem;">
                    <label style="display:block;font-size:0.8rem;font-weight:600;color:var(--text-secondary);margin-bottom:0.5rem;text-transform:uppercase;letter-spacing:0.05em;">Email</label>
                    <input type="email" name="email" class="input-field" placeholder="you@example.com" value="{{ old('email') }}" required>
                    @error('email')
                    <span style="font-size:0.75rem;color:#EF4444;margin-top:0.25rem;display:block;">{{ $message }}</span>
                    @enderror
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:var(--text-secondary);margin-bottom:0.5rem;text-transform:uppercase;letter-spacing:0.05em;">Password</label>
                        <input type="password" name="password" class="input-field" placeholder="••••••••" required>
                        @error('password')
                        <span style="font-size:0.75rem;color:#EF4444;margin-top:0.25rem;display:block;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:var(--text-secondary);margin-bottom:0.5rem;text-transform:uppercase;letter-spacing:0.05em;">Confirm</label>
                        <input type="password" name="password_confirmation" class="input-field" placeholder="••••••••" required>
                    </div>
                </div>

                <div style="margin-bottom:1.5rem;">
                    <label style="display:flex;align-items:flex-start;gap:0.5rem;cursor:pointer;">
                        <input type="checkbox" name="terms" style="accent-color:var(--accent-cyan);margin-top:3px;" required>
                        <span style="font-size:0.8rem;color:var(--text-secondary);line-height:1.5;">I agree to the <a href="#" style="color:var(--accent-cyan);text-decoration:none;">Terms of Service</a> and <a href="#" style="color:var(--accent-cyan);text-decoration:none;">Privacy Policy</a></span>
                    </label>
                </div>

                <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:0.9rem;">
                    Create Account
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </button>
            </form>

            <div style="margin-top:1.5rem;text-align:center;">
                <p style="font-size:0.85rem;color:var(--text-muted);">
                    Already have an account?
                    <a href="{{ route('login') }}" style="color:var(--accent-cyan);text-decoration:none;font-weight:600;">Sign In</a>
                </p>
            </div>
        </div>
    </div>
</section>
@endsection
