<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit User | Curevia Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background:#0B0F14;color:#F0F4FF;font-family:'Inter',system-ui,sans-serif;">

@include('admin.layouts.sidebar')

<main style="margin-left:260px;padding:2rem;max-width:900px;" id="admin-main">

    <button id="admin-sidebar-toggle" onclick="document.getElementById('admin-sidebar').classList.toggle('open')" style="display:none;position:fixed;top:1rem;left:1rem;z-index:1001;width:40px;height:40px;border-radius:10px;background:rgba(17,24,39,0.9);border:1px solid var(--border-glow);color:var(--accent-cyan);cursor:pointer;align-items:center;justify-content:center;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>

    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:2rem;">
        <a href="{{ route('admin.users.index') }}" style="color:var(--text-muted);text-decoration:none;font-size:0.85rem;">← Users</a>
        <span style="color:var(--border-subtle);">/</span>
        <h1 style="font-size:1.4rem;font-weight:800;">Edit User</h1>
    </div>

    @if($errors->any())
    <div style="background:rgba(239,68,68,0.1);border:1px solid #f87171;color:#f87171;padding:1rem;border-radius:0.75rem;margin-bottom:1.5rem;">
        <ul style="margin:0;padding-left:1.25rem;">
            @foreach($errors->all() as $err)<li style="font-size:0.875rem;">{{ $err }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- User info card --}}
    <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;padding:1.5rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:1.25rem;">
        <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--accent-cyan),var(--accent-violet));display:flex;align-items:center;justify-content:center;font-size:1.25rem;font-weight:800;color:var(--bg-primary);">{{ strtoupper(substr($user->name,0,1)) }}</div>
        <div>
            <div style="font-size:1rem;font-weight:700;color:var(--text-primary);">{{ $user->name }}</div>
            <div style="font-size:0.8rem;color:var(--text-muted);">Joined {{ $user->created_at->format('M d, Y') }} · ID #{{ $user->id }}</div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf @method('PUT')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

            <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;padding:1.5rem;">
                <h3 style="font-size:0.875rem;font-weight:700;margin-bottom:1rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;">Account Info</h3>

                <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Full Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.65rem 0.875rem;color:var(--text-primary);font-size:0.875rem;outline:none;box-sizing:border-box;margin-bottom:1rem;">

                <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.65rem 0.875rem;color:var(--text-primary);font-size:0.875rem;outline:none;box-sizing:border-box;margin-bottom:1rem;">

                <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Role</label>
                <select name="role" required style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.65rem 0.875rem;color:var(--text-primary);font-size:0.875rem;outline:none;margin-bottom:1.5rem;">
                    <option value="user"        {{ old('role',$user->role)==='user'        ?'selected':'' }}>User</option>
                    <option value="contributor" {{ old('role',$user->role)==='contributor' ?'selected':'' }}>Contributor</option>
                    <option value="admin"       {{ old('role',$user->role)==='admin'       ?'selected':'' }}>Admin</option>
                </select>

                <button type="submit" class="btn-primary" style="width:100%;padding:0.7rem;font-size:0.875rem;font-weight:700;">Save Changes</button>
            </div>

            <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;padding:1.5rem;">
                <h3 style="font-size:0.875rem;font-weight:700;margin-bottom:1rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;">Reset Password</h3>
                <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:1rem;">Leave blank to keep current password.</p>

                <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">New Password</label>
                <input type="password" name="password" style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.65rem 0.875rem;color:var(--text-primary);font-size:0.875rem;outline:none;box-sizing:border-box;margin-bottom:1rem;">

                <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Confirm Password</label>
                <input type="password" name="password_confirmation" style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.65rem 0.875rem;color:var(--text-primary);font-size:0.875rem;outline:none;box-sizing:border-box;margin-bottom:1.5rem;">

                @if($user->id !== auth()->id())
                <div style="border-top:1px solid var(--border-subtle);padding-top:1.25rem;margin-top:0.25rem;">
                    <p style="font-size:0.75rem;color:#f87171;margin-bottom:0.75rem;font-weight:600;">DANGER ZONE</p>
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Permanently delete {{ addslashes($user->name) }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="width:100%;padding:0.6rem;font-size:0.8rem;font-weight:600;background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);border-radius:0.5rem;color:#f87171;cursor:pointer;">Delete User</button>
                    </form>
                </div>
                @endif
            </div>

        </div>
    </form>
</main>

<style>
@media (max-width: 1024px) {
    #admin-sidebar { transform: translateX(-100%); position: fixed; top:0; left:0; bottom:0; z-index:1000; transition: transform 0.3s ease; }
    #admin-sidebar.open { transform: translateX(0); }
    #admin-main { margin-left: 0 !important; }
    #admin-sidebar-toggle { display: flex !important; }
    #admin-main > form > div { grid-template-columns: 1fr !important; }
}
@media (max-width: 640px) {
    #admin-main { padding: 1rem !important; padding-top: 3.5rem !important; }
}
</style>
</body>
</html>
