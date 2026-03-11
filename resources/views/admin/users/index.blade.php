<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Users | Curevia Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background:#0B0F14;color:#F0F4FF;font-family:'Inter',system-ui,sans-serif;">

@include('admin.layouts.sidebar')

<main style="margin-left:260px;padding:2rem;" id="admin-main">

    <button id="admin-sidebar-toggle" onclick="document.getElementById('admin-sidebar').classList.toggle('open')" style="display:none;position:fixed;top:1rem;left:1rem;z-index:1001;width:40px;height:40px;border-radius:10px;background:rgba(17,24,39,0.9);border:1px solid var(--border-glow);color:var(--accent-cyan);cursor:pointer;align-items:center;justify-content:center;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;">
        <div>
            <h1 style="font-size:1.5rem;font-weight:800;">Users</h1>
            <p style="font-size:0.85rem;color:var(--text-muted);">{{ $users->total() }} registered users</p>
        </div>
    </div>

    @if(session('success'))
    <div style="background:rgba(34,242,226,0.1);border:1px solid var(--accent-cyan);color:var(--accent-cyan);padding:0.75rem 1rem;border-radius:0.75rem;margin-bottom:1.5rem;font-size:0.875rem;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div style="background:rgba(239,68,68,0.1);border:1px solid #f87171;color:#f87171;padding:0.75rem 1rem;border-radius:0.75rem;margin-bottom:1.5rem;font-size:0.875rem;">{{ session('error') }}</div>
    @endif

    {{-- Filters --}}
    <form method="GET" style="display:flex;gap:0.75rem;margin-bottom:1.5rem;flex-wrap:wrap;">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name or email…" style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.5rem 0.875rem;color:var(--text-primary);font-size:0.875rem;min-width:220px;outline:none;">
        <select name="role" style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.5rem 0.875rem;color:var(--text-primary);font-size:0.875rem;outline:none;">
            <option value="">All Roles</option>
            <option value="admin" {{ request('role')==='admin'?'selected':'' }}>Admin</option>
            <option value="contributor" {{ request('role')==='contributor'?'selected':'' }}>Contributor</option>
            <option value="user" {{ request('role')==='user'?'selected':'' }}>User</option>
        </select>
        <button type="submit" class="btn-primary" style="padding:0.5rem 1rem;font-size:0.875rem;">Filter</button>
        @if(request()->anyFilled(['q','role']))
        <a href="{{ route('admin.users.index') }}" style="padding:0.5rem 1rem;font-size:0.875rem;color:var(--text-muted);text-decoration:none;border:1px solid var(--border-subtle);border-radius:0.5rem;">Clear</a>
        @endif
    </form>

    <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;overflow:hidden;">
        <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;min-width:600px;">
            <thead>
                <tr style="border-bottom:1px solid var(--border-subtle);background:rgba(0,0,0,0.2);">
                    <th style="text-align:left;padding:1rem 1.25rem;font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">User</th>
                    <th style="text-align:left;padding:1rem 1rem;font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">Role</th>
                    <th style="text-align:left;padding:1rem 1rem;font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">Joined</th>
                    <th style="text-align:right;padding:1rem 1.25rem;font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr style="border-bottom:1px solid var(--border-subtle);transition:background 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='transparent'">
                    <td style="padding:0.875rem 1.25rem;">
                        <div style="display:flex;align-items:center;gap:0.75rem;">
                            <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--accent-cyan),var(--accent-violet));display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:800;color:var(--bg-primary);flex-shrink:0;">{{ strtoupper(substr($user->name,0,1)) }}</div>
                            <div>
                                <div style="font-size:0.875rem;font-weight:600;color:var(--text-primary);">{{ $user->name }}</div>
                                <div style="font-size:0.75rem;color:var(--text-muted);">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="padding:0.875rem 1rem;">
                        @php $rc = ['admin'=>'background:rgba(239,68,68,0.1);color:#f87171;','contributor'=>'background:rgba(124,108,255,0.1);color:var(--accent-violet);','user'=>'background:rgba(34,242,226,0.08);color:var(--accent-cyan);'][$user->role] ?? ''; @endphp
                        <span style="font-size:0.7rem;font-weight:600;padding:0.2rem 0.6rem;border-radius:100px;{{ $rc }}">{{ ucfirst($user->role) }}</span>
                    </td>
                    <td style="padding:0.875rem 1rem;font-size:0.8rem;color:var(--text-muted);">{{ $user->created_at->format('M d, Y') }}</td>
                    <td style="padding:0.875rem 1.25rem;text-align:right;">
                        <div style="display:flex;gap:0.5rem;justify-content:flex-end;">
                            <a href="{{ route('admin.users.edit', $user) }}" style="padding:0.35rem 0.75rem;font-size:0.75rem;border-radius:0.4rem;background:rgba(34,242,226,0.1);color:var(--accent-cyan);text-decoration:none;font-weight:600;">Edit</a>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete {{ addslashes($user->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="padding:0.35rem 0.75rem;font-size:0.75rem;border-radius:0.4rem;background:rgba(239,68,68,0.1);color:#f87171;border:none;cursor:pointer;font-weight:600;">Delete</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="padding:3rem;text-align:center;color:var(--text-muted);font-size:0.875rem;">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($users->hasPages())
        <div style="padding:1rem 1.25rem;border-top:1px solid var(--border-subtle);display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:0.8rem;color:var(--text-muted);">Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}</span>
            <div style="display:flex;gap:0.5rem;">
                @if($users->onFirstPage())
                <span style="padding:0.35rem 0.75rem;font-size:0.8rem;border-radius:0.4rem;border:1px solid var(--border-subtle);color:var(--text-muted);">← Prev</span>
                @else
                <a href="{{ $users->previousPageUrl() }}" style="padding:0.35rem 0.75rem;font-size:0.8rem;border-radius:0.4rem;border:1px solid var(--border-subtle);color:var(--text-primary);text-decoration:none;">← Prev</a>
                @endif
                @if($users->hasMorePages())
                <a href="{{ $users->nextPageUrl() }}" style="padding:0.35rem 0.75rem;font-size:0.8rem;border-radius:0.4rem;border:1px solid var(--border-subtle);color:var(--text-primary);text-decoration:none;">Next →</a>
                @else
                <span style="padding:0.35rem 0.75rem;font-size:0.8rem;border-radius:0.4rem;border:1px solid var(--border-subtle);color:var(--text-muted);">Next →</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</main>

<style>
@media (max-width: 1024px) {
    #admin-sidebar { transform: translateX(-100%); position: fixed; top:0; left:0; bottom:0; z-index:1000; transition: transform 0.3s ease; }
    #admin-sidebar.open { transform: translateX(0); }
    #admin-main { margin-left: 0 !important; }
    #admin-sidebar-toggle { display: flex !important; }
}
@media (max-width: 640px) {
    #admin-main { padding: 1rem !important; padding-top: 3.5rem !important; }
}
</style>
</body>
</html>
