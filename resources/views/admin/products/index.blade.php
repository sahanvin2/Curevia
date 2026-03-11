<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Products — Curevia Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background:#0B0F14;color:#F0F4FF;font-family:'Inter',system-ui,sans-serif;">

{{-- Sidebar --}}
<aside class="sidebar" id="admin-sidebar">
    <div style="padding:1.5rem;">
        <a href="{{ route('home') }}" class="logo-text" style="font-size:1.25rem;text-decoration:none;display:block;margin-bottom:0.5rem;">Curevia</a>
        <span style="font-size:0.65rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;font-weight:700;">Admin Panel</span>
    </div>
    <nav style="padding:0 0.75rem;margin-top:1rem;">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
            Dashboard
        </a>
        <a href="{{ route('admin.products.index') }}" class="sidebar-link active">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            Products
        </a>
    </nav>
    <div style="position:absolute;bottom:1.5rem;left:0;right:0;padding:0 0.75rem;">
        <a href="{{ route('home') }}" class="sidebar-link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Back to Site
        </a>
    </div>
</aside>

<main style="margin-left:260px;padding:2rem;">

    {{-- Top Bar --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;flex-wrap:wrap;gap:1rem;">
        <div>
            <h1 style="font-size:1.5rem;font-weight:800;color:var(--text-primary);">Products</h1>
            <p style="font-size:0.85rem;color:var(--text-muted);">Manage your dropship product catalogue and affiliate links.</p>
        </div>
        <a href="{{ route('admin.products.create') }}"
            style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.5rem;background:linear-gradient(135deg,var(--accent-cyan),var(--accent-violet));border-radius:0.75rem;color:var(--bg-primary);font-size:0.88rem;font-weight:700;text-decoration:none;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Product
        </a>
    </div>

    @if(session('success'))
    <div style="background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.3);border-radius:0.75rem;padding:0.9rem 1.25rem;display:flex;align-items:center;gap:0.6rem;margin-bottom:1.5rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <span style="font-size:0.88rem;color:#22C55E;font-weight:600;">{{ session('success') }}</span>
    </div>
    @endif

    {{-- Affiliate URL warning for products without links --}}
    @php $noLinkCount = $products->whereNull('affiliate_url')->count(); @endphp
    @if($noLinkCount > 0)
    <div style="background:rgba(245,158,11,0.06);border:1px solid rgba(245,158,11,0.2);border-radius:0.75rem;padding:0.9rem 1.25rem;display:flex;align-items:center;gap:0.75rem;margin-bottom:1.5rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <span style="font-size:0.85rem;color:#F59E0B;font-weight:600;">{{ $noLinkCount }} product{{ $noLinkCount > 1 ? 's' : '' }} missing affiliate link — "Buy Now" won't work until set.</span>
    </div>
    @endif

    {{-- Products table --}}
    <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid var(--border-subtle);">
                    <th style="text-align:left;padding:1rem 1.25rem;font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:700;width:35%;">Product</th>
                    <th style="text-align:left;padding:1rem 1.25rem;font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">Category</th>
                    <th style="text-align:left;padding:1rem 1.25rem;font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">Price</th>
                    <th style="text-align:left;padding:1rem 1.25rem;font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">Affiliate Link</th>
                    <th style="text-align:left;padding:1rem 1.25rem;font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">Status</th>
                    <th style="text-align:right;padding:1rem 1.25rem;font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr style="border-bottom:1px solid rgba(255,255,255,0.04);transition:background .15s;" onmouseover="this.style.background='rgba(34,242,226,0.02)'" onmouseout="this.style.background='transparent'">
                    {{-- Product name + image --}}
                    <td style="padding:0.9rem 1.25rem;">
                        <div style="display:flex;align-items:center;gap:0.75rem;">
                            @if($product->image)
                            <img src="{{ $product->image }}" alt="{{ $product->name }}" style="width:44px;height:44px;border-radius:0.5rem;object-fit:cover;flex-shrink:0;background:rgba(17,24,39,0.8);">
                            @endif
                            <div>
                                <div style="font-size:0.88rem;font-weight:600;color:var(--text-primary);line-height:1.3;">{{ Str::limit($product->name, 45) }}</div>
                                <div style="font-size:0.72rem;color:var(--text-muted);margin-top:0.1rem;">{{ $product->slug }}</div>
                            </div>
                        </div>
                    </td>
                    {{-- Category --}}
                    <td style="padding:0.9rem 1.25rem;">
                        <span style="font-size:0.75rem;font-weight:700;color:var(--accent-violet);background:rgba(124,108,255,0.1);padding:0.2rem 0.6rem;border-radius:100px;">{{ $product->category }}</span>
                    </td>
                    {{-- Price --}}
                    <td style="padding:0.9rem 1.25rem;">
                        <span style="font-size:0.9rem;font-weight:700;color:var(--accent-cyan)">${{ number_format($product->price, 2) }}</span>
                        @if($product->original_price)
                        <span style="font-size:0.75rem;color:var(--text-muted);text-decoration:line-through;display:block;">${{ number_format($product->original_price, 2) }}</span>
                        @endif
                    </td>
                    {{-- Affiliate link status --}}
                    <td style="padding:0.9rem 1.25rem;">
                        @if($product->affiliate_url)
                        @php
                            $store = 'Other';
                            $storeColor = 'var(--accent-cyan)';
                            if (str_contains($product->affiliate_url, 'amazon'))     { $store = 'Amazon';     $storeColor = '#FF9900'; }
                            elseif (str_contains($product->affiliate_url, 'aliexpress')) { $store = 'AliExpress'; $storeColor = '#FF4747'; }
                            elseif (str_contains($product->affiliate_url, 'ebay'))   { $store = 'eBay';       $storeColor = '#0064D3'; }
                        @endphp
                        <div style="display:flex;align-items:center;gap:0.4rem;">
                            <span style="width:7px;height:7px;border-radius:50%;background:#22C55E;flex-shrink:0;"></span>
                            <span style="font-size:0.78rem;font-weight:700;color:{{ $storeColor }};">{{ $store }}</span>
                        </div>
                        <a href="{{ $product->affiliate_url }}" target="_blank" rel="noopener noreferrer" style="font-size:0.68rem;color:var(--text-muted);text-decoration:none;word-break:break-all;display:block;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-top:0.15rem;" title="{{ $product->affiliate_url }}">{{ Str::limit($product->affiliate_url, 35) }}</a>
                        @else
                        <div style="display:flex;align-items:center;gap:0.4rem;">
                            <span style="width:7px;height:7px;border-radius:50%;background:#F59E0B;flex-shrink:0;"></span>
                            <span style="font-size:0.78rem;color:#F59E0B;font-weight:600;">Not set</span>
                        </div>
                        @endif
                    </td>
                    {{-- Active status --}}
                    <td style="padding:0.9rem 1.25rem;">
                        @if($product->is_active)
                        <span style="font-size:0.72rem;font-weight:700;color:#22C55E;background:rgba(34,197,94,0.1);padding:0.2rem 0.6rem;border-radius:100px;">Active</span>
                        @else
                        <span style="font-size:0.72rem;font-weight:700;color:var(--text-muted);background:rgba(255,255,255,0.05);padding:0.2rem 0.6rem;border-radius:100px;">Inactive</span>
                        @endif
                    </td>
                    {{-- Actions --}}
                    <td style="padding:0.9rem 1.25rem;text-align:right;">
                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:0.5rem;">
                            <a href="{{ route('shop.show', $product->slug) }}" target="_blank"
                                style="padding:0.35rem 0.7rem;background:rgba(34,242,226,0.06);border:1px solid rgba(34,242,226,0.15);border-radius:0.4rem;color:var(--accent-cyan);font-size:0.75rem;font-weight:600;text-decoration:none;white-space:nowrap;">
                                Preview
                            </a>
                            <a href="{{ route('admin.products.edit', $product) }}"
                                style="padding:0.35rem 0.7rem;background:rgba(124,108,255,0.06);border:1px solid rgba(124,108,255,0.2);border-radius:0.4rem;color:var(--accent-violet);font-size:0.75rem;font-weight:600;text-decoration:none;white-space:nowrap;">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" style="display:inline;" onsubmit="return confirm('Delete {{ addslashes($product->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="padding:0.35rem 0.7rem;background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.2);border-radius:0.4rem;color:#F87171;font-size:0.75rem;font-weight:600;cursor:pointer;">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p style="font-size:0.78rem;color:var(--text-muted);text-align:center;margin-top:1.25rem;">{{ $products->count() }} product{{ $products->count() !== 1 ? 's' : '' }} total</p>
</main>
</body>
</html>
