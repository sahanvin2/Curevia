@extends('layouts.app')

@section('title', 'Login | Curevia')

@section('content')
<section style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:6rem 1.5rem 3rem;">
    <div style="width:100%;max-width:440px;">
        <div class="glass-card" style="padding:2.5rem;border-radius:1.5rem;">
            <div style="text-align:center;margin-bottom:2rem;">
                <a href="{{ route('home') }}" class="logo-text" style="font-size:1.75rem;text-decoration:none;display:block;margin-bottom:0.75rem;">Curevia</a>
                <p style="color:var(--text-secondary);font-size:0.9rem;">Welcome back, explorer.</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div style="margin-bottom:1.25rem;">
                    <label style="display:block;font-size:0.8rem;font-weight:600;color:var(--text-secondary);margin-bottom:0.5rem;text-transform:uppercase;letter-spacing:0.05em;">Email</label>
                    <input type="email" name="email" class="input-field" placeholder="you@example.com" value="{{ old('email') }}" required autofocus>
                    @error('email')
                    <span style="font-size:0.75rem;color:#EF4444;margin-top:0.25rem;display:block;">{{ $message }}</span>
                    @enderror
                </div>

                <div style="margin-bottom:1.25rem;">
                    <label style="display:block;font-size:0.8rem;font-weight:600;color:var(--text-secondary);margin-bottom:0.5rem;text-transform:uppercase;letter-spacing:0.05em;">Password</label>
                    <input type="password" name="password" class="input-field" placeholder="••••••••" required>
                    @error('password')
                    <span style="font-size:0.75rem;color:#EF4444;margin-top:0.25rem;display:block;">{{ $message }}</span>
                    @enderror
                </div>

                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                        <input type="checkbox" name="remember" style="accent-color:var(--accent-cyan);">
                        <span style="font-size:0.8rem;color:var(--text-secondary);">Remember me</span>
                    </label>
                    <a href="#" style="font-size:0.8rem;color:var(--accent-cyan);text-decoration:none;">Forgot password?</a>
                </div>

                <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:0.9rem;">
                    Sign In
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </button>
            </form>

            <div style="margin-top:1.5rem;text-align:center;">
                <p style="font-size:0.85rem;color:var(--text-muted);">
                    Don't have an account?
                    <a href="{{ route('register') }}" style="color:var(--accent-cyan);text-decoration:none;font-weight:600;">Join Free</a>
                </p>
            </div>
        </div>
    </div>
</section>
@endsection
