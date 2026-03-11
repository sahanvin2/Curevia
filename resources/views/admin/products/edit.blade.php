<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $product->exists ? 'Edit: '.$product->name : 'Add Product' }} — Curevia Admin</title>
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

<main style="margin-left:260px;padding:2rem;max-width:calc(100% - 260px);">

    {{-- Breadcrumb --}}
    <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.8rem;color:var(--text-muted);margin-bottom:1.5rem;">
        <a href="{{ route('admin.products.index') }}" style="color:var(--text-muted);text-decoration:none;" onmouseover="this.style.color='var(--accent-cyan)'" onmouseout="this.style.color='var(--text-muted)'">Products</a>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="9 18 15 12 9 6"/></svg>
        <span>{{ $product->exists ? 'Edit' : 'New Product' }}</span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:2rem;align-items:start;">

        {{-- Main form --}}
        <div>
            <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;padding:2rem;">

                <h1 style="font-size:1.35rem;font-weight:800;color:var(--text-primary);margin-bottom:0.5rem;">
                    {{ $product->exists ? 'Edit Product' : 'Add New Product' }}
                </h1>
                <p style="font-size:0.83rem;color:var(--text-muted);margin-bottom:2rem;">Fill in the product details. The <strong style="color:#F59E0B;">Affiliate Link</strong> powers the "Buy Now" button for users.</p>

                @if($errors->any())
                <div style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);border-radius:0.75rem;padding:1rem 1.25rem;margin-bottom:1.5rem;">
                    <p style="font-size:0.82rem;font-weight:700;color:#F87171;margin-bottom:0.5rem;">Please fix the following:</p>
                    <ul style="margin:0;padding-left:1.25rem;">
                        @foreach($errors->all() as $e)
                        <li style="font-size:0.8rem;color:#F87171;padding:0.2rem 0;">{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
                    @csrf
                    @if($product->exists) @method('PUT') @endif

                    {{-- ★ AFFILIATE LINK — most important field, shown at top --}}
                    <div style="background:rgba(245,158,11,0.04);border:1px solid rgba(245,158,11,0.2);border-radius:1rem;padding:1.25rem;margin-bottom:2rem;">
                        <label style="display:flex;align-items:center;gap:0.5rem;font-size:0.8rem;font-weight:800;color:#F59E0B;text-transform:uppercase;letter-spacing:.08em;margin-bottom:0.6rem;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                            Affiliate / Buy Now Link <span style="color:var(--text-muted);font-weight:500;text-transform:none;letter-spacing:0;">(required for Buy Now button)</span>
                        </label>
                        <input type="url" name="affiliate_url" value="{{ old('affiliate_url', $product->affiliate_url) }}"
                            placeholder="https://www.amazon.com/dp/... or https://www.aliexpress.com/..."
                            style="width:100%;background:rgba(11,15,20,0.9);border:1px solid rgba(245,158,11,0.3);border-radius:0.75rem;padding:0.8rem 1rem;color:var(--text-primary);font-size:0.88rem;outline:none;box-sizing:border-box;font-family:monospace;transition:border-color .2s;"
                            onfocus="this.style.borderColor='#F59E0B'" onblur="this.style.borderColor='rgba(245,158,11,0.3)'" id="aff-url-input">
                        <div id="aff-url-preview" style="margin-top:0.6rem;font-size:0.75rem;color:var(--text-muted);min-height:1.2rem;"></div>
                    </div>

                    {{-- Product name --}}
                    <div style="margin-bottom:1.25rem;">
                        <label style="display:block;font-size:0.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:0.45rem;">Product Name *</label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" required placeholder="e.g. Celestron NexStar 8SE Telescope"
                            style="width:100%;background:rgba(11,15,20,0.8);border:1px solid var(--border-subtle);border-radius:0.75rem;padding:0.75rem 1rem;color:var(--text-primary);font-size:0.88rem;outline:none;box-sizing:border-box;transition:border-color .2s;"
                            onfocus="this.style.borderColor='var(--accent-cyan)'" onblur="this.style.borderColor='var(--border-subtle)'">
                    </div>

                    {{-- Slug (only for new) --}}
                    @if(!$product->exists)
                    <div style="margin-bottom:1.25rem;">
                        <label style="display:block;font-size:0.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:0.45rem;">URL Slug <span style="color:var(--text-muted);font-weight:400;">(auto-generated if blank)</span></label>
                        <input type="text" name="slug" value="{{ old('slug') }}" placeholder="e.g. celestron-nexstar-telescope"
                            style="width:100%;background:rgba(11,15,20,0.8);border:1px solid var(--border-subtle);border-radius:0.75rem;padding:0.75rem 1rem;color:var(--text-primary);font-size:0.88rem;outline:none;box-sizing:border-box;font-family:monospace;transition:border-color .2s;"
                            onfocus="this.style.borderColor='var(--accent-cyan)'" onblur="this.style.borderColor='var(--border-subtle)'">
                    </div>
                    @endif

                    {{-- Short description --}}
                    <div style="margin-bottom:1.25rem;">
                        <label style="display:block;font-size:0.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:0.45rem;">Short Description</label>
                        <textarea name="description" rows="3" placeholder="One-sentence product summary shown on cards"
                            style="width:100%;background:rgba(11,15,20,0.8);border:1px solid var(--border-subtle);border-radius:0.75rem;padding:0.75rem 1rem;color:var(--text-primary);font-size:0.88rem;outline:none;box-sizing:border-box;resize:vertical;font-family:inherit;transition:border-color .2s;"
                            onfocus="this.style.borderColor='var(--accent-cyan)'" onblur="this.style.borderColor='var(--border-subtle)'"
                        >{{ old('description', $product->description) }}</textarea>
                    </div>

                    {{-- Price row --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">
                        <div>
                            <label style="display:block;font-size:0.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:0.45rem;">Price (USD) *</label>
                            <input type="number" name="price" value="{{ old('price', $product->price) }}" required step="0.01" min="0" placeholder="0.00"
                                style="width:100%;background:rgba(11,15,20,0.8);border:1px solid var(--border-subtle);border-radius:0.75rem;padding:0.75rem 1rem;color:var(--text-primary);font-size:0.88rem;outline:none;box-sizing:border-box;transition:border-color .2s;"
                                onfocus="this.style.borderColor='var(--accent-cyan)'" onblur="this.style.borderColor='var(--border-subtle)'">
                        </div>
                        <div>
                            <label style="display:block;font-size:0.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:0.45rem;">Original Price <span style="font-weight:400;">(for strike-through)</span></label>
                            <input type="number" name="original_price" value="{{ old('original_price', $product->original_price) }}" step="0.01" min="0" placeholder="Leave blank if no discount"
                                style="width:100%;background:rgba(11,15,20,0.8);border:1px solid var(--border-subtle);border-radius:0.75rem;padding:0.75rem 1rem;color:var(--text-primary);font-size:0.88rem;outline:none;box-sizing:border-box;transition:border-color .2s;"
                                onfocus="this.style.borderColor='var(--accent-cyan)'" onblur="this.style.borderColor='var(--border-subtle)'">
                        </div>
                    </div>

                    {{-- Category + Badge row --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">
                        <div>
                            <label style="display:block;font-size:0.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:0.45rem;">Category</label>
                            <select name="category"
                                style="width:100%;background:rgba(11,15,20,0.8);border:1px solid var(--border-subtle);border-radius:0.75rem;padding:0.75rem 1rem;color:var(--text-primary);font-size:0.88rem;outline:none;box-sizing:border-box;appearance:none;cursor:pointer;">
                                @foreach(['Astronomy','Space','Science','Technology','History','Nature','Books'] as $cat)
                                <option value="{{ $cat }}" {{ old('category', $product->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label style="display:block;font-size:0.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:0.45rem;">Badge <span style="font-weight:400;">(optional)</span></label>
                            <input type="text" name="badge" value="{{ old('badge', $product->badge) }}" placeholder="e.g. Best Seller, Sale, New"
                                style="width:100%;background:rgba(11,15,20,0.8);border:1px solid var(--border-subtle);border-radius:0.75rem;padding:0.75rem 1rem;color:var(--text-primary);font-size:0.88rem;outline:none;box-sizing:border-box;transition:border-color .2s;"
                                onfocus="this.style.borderColor='var(--accent-cyan)'" onblur="this.style.borderColor='var(--border-subtle)'">
                        </div>
                    </div>

                    {{-- Image URL --}}
                    <div style="margin-bottom:1.75rem;">
                        <label style="display:block;font-size:0.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:0.45rem;">Product Image URL</label>
                        <input type="url" name="image" value="{{ old('image', $product->image) }}" placeholder="https://..."
                            style="width:100%;background:rgba(11,15,20,0.8);border:1px solid var(--border-subtle);border-radius:0.75rem;padding:0.75rem 1rem;color:var(--text-primary);font-size:0.88rem;outline:none;box-sizing:border-box;font-family:monospace;transition:border-color .2s;"
                            onfocus="this.style.borderColor='var(--accent-cyan)'" onblur="this.style.borderColor='var(--border-subtle)'">
                    </div>

                    {{-- Active toggle --}}
                    <div style="display:flex;align-items:center;gap:1rem;padding:1rem;background:rgba(255,255,255,0.02);border-radius:0.75rem;margin-bottom:1.75rem;">
                        <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $product->exists ? $product->is_active : true) ? 'checked' : '' }}
                            style="width:18px;height:18px;cursor:pointer;accent-color:var(--accent-cyan);">
                        <div>
                            <label for="is_active" style="font-size:0.88rem;font-weight:700;color:var(--text-primary);cursor:pointer;">Active / Visible</label>
                            <p style="font-size:0.75rem;color:var(--text-muted);margin:0;">Uncheck to hide this product from the shop.</p>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                        <button type="submit"
                            style="flex:1;padding:0.85rem;background:linear-gradient(135deg,var(--accent-cyan),var(--accent-violet));border:none;border-radius:0.75rem;color:var(--bg-primary);font-size:0.95rem;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:0.5rem;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            {{ $product->exists ? 'Save Changes' : 'Create Product' }}
                        </button>
                        <a href="{{ route('admin.products.index') }}"
                            style="padding:0.85rem 2rem;background:transparent;border:1px solid var(--border-subtle);border-radius:0.75rem;color:var(--text-secondary);font-size:0.95rem;font-weight:700;text-decoration:none;display:flex;align-items:center;justify-content:center;">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Right sidebar: Tips + Preview --}}
        <div style="position:sticky;top:2rem;">

            {{-- Affiliate link tips --}}
            <div style="background:rgba(245,158,11,0.04);border:1px solid rgba(245,158,11,0.15);border-radius:1.25rem;padding:1.5rem;margin-bottom:1.25rem;">
                <h3 style="font-size:0.82rem;font-weight:800;color:#F59E0B;text-transform:uppercase;letter-spacing:.08em;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Affiliate Link Tips
                </h3>
                @foreach([
                    ['Amazon', '#FF9900', 'amazon.com', 'amazon.com/dp/ASIN or amazon.com/s?k=...'],
                    ['AliExpress', '#FF4747', 'aliexpress.com', 'aliexpress.com/item/...'],
                    ['eBay', '#0064D3', 'ebay.com', 'ebay.com/itm/...'],
                ] as [$name,$color,$domain,$example])
                <div style="padding:0.6rem 0;{{ !$loop->last ? 'border-bottom:1px solid rgba(255,255,255,0.05);' : '' }}">
                    <div style="font-size:0.78rem;font-weight:700;color:{{ $color }};">{{ $name }}</div>
                    <div style="font-size:0.72rem;color:var(--text-muted);margin-top:0.1rem;font-family:monospace;">{{ $example }}</div>
                </div>
                @endforeach
            </div>

            {{-- Image preview --}}
            @if($product->image)
            <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;padding:1rem;">
                <p style="font-size:0.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;font-weight:700;margin-bottom:0.75rem;">Current Image</p>
                <img src="{{ $product->image }}" alt="{{ $product->name }}" style="width:100%;border-radius:0.75rem;aspect-ratio:1;object-fit:cover;">
            </div>
            @endif

        </div>
    </div>
</main>

<script>
// Live marketplace detection for affiliate URL field
const affInput = document.getElementById('aff-url-input');
const affPreview = document.getElementById('aff-url-preview');
if (affInput) {
    function updatePreview() {
        const url = affInput.value.trim();
        if (!url) { affPreview.textContent = ''; return; }
        let name = 'Partner store', color = 'var(--accent-cyan)';
        if (url.includes('amazon'))     { name = '✓ Amazon detected';     color = '#FF9900'; }
        else if (url.includes('aliexpress')) { name = '✓ AliExpress detected'; color = '#FF4747'; }
        else if (url.includes('ebay'))  { name = '✓ eBay detected';       color = '#0064D3'; }
        else { name = '✓ Custom store link'; }
        affPreview.innerHTML = '<span style="color:' + color + ';font-weight:700;">' + name + '</span>';
    }
    affInput.addEventListener('input', updatePreview);
    updatePreview();
}
</script>
</body>
</html>
