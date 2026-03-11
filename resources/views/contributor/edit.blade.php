@extends('layouts.app')

@section('title', ($article->exists ? 'Edit Article' : 'Write Article') . ' | Curevia')

@section('content')
<section style="padding:7rem 0 3rem;position:relative;z-index:1;">
<div style="max-width:1100px;margin:0 auto;padding:0 1.5rem;">

    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:2rem;">
        <a href="{{ route('contributor.articles') }}" style="color:var(--text-muted);text-decoration:none;font-size:0.875rem;">← My Articles</a>
        <span style="color:var(--border-subtle);">/</span>
        <h1 style="font-size:1.4rem;font-weight:800;color:var(--text-primary);">{{ $article->exists ? 'Edit Article' : 'Write New Article' }}</h1>
    </div>

    @if($errors->any())
    <div style="background:rgba(239,68,68,0.1);border:1px solid #f87171;color:#f87171;padding:1rem;border-radius:0.75rem;margin-bottom:1.5rem;">
        <ul style="margin:0;padding-left:1.25rem;">
            @foreach($errors->all() as $err)<li style="font-size:0.875rem;">{{ $err }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ $article->exists ? route('contributor.articles.update', $article) : route('contributor.articles.store') }}">
        @csrf
        @if($article->exists) @method('PUT') @endif

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">

            <div style="display:flex;flex-direction:column;gap:1.25rem;">
                <div class="glass-card" style="padding:1.5rem;">
                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;font-weight:600;">Title *</label>
                    <input type="text" name="title" value="{{ old('title', $article->title) }}" required placeholder="Enter article title…"
                        style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.75rem 1rem;color:var(--text-primary);font-size:1rem;outline:none;box-sizing:border-box;margin-bottom:1.25rem;font-weight:600;">

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;font-weight:600;">Summary *</label>
                    <textarea name="summary" required rows="3" placeholder="A brief description of the article…"
                        style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.75rem 1rem;color:var(--text-primary);font-size:0.875rem;outline:none;resize:vertical;box-sizing:border-box;margin-bottom:1.25rem;">{{ old('summary', $article->summary) }}</textarea>

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;font-weight:600;">Content * <span style="font-weight:400;font-size:0.75rem;">(HTML supported)</span></label>
                    <textarea name="content" required rows="24" placeholder="Write your article content here…"
                        style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.75rem 1rem;color:var(--text-primary);font-size:0.85rem;outline:none;resize:vertical;box-sizing:border-box;line-height:1.7;">{{ old('content', $article->content) }}</textarea>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:1.25rem;">
                <div class="glass-card" style="padding:1.5rem;">
                    <h3 style="font-size:0.875rem;font-weight:700;margin-bottom:1rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;">Publish Settings</h3>

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Status</label>
                    <select name="status" required style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.65rem 0.875rem;color:var(--text-primary);font-size:0.875rem;outline:none;margin-bottom:1rem;">
                        <option value="draft"  {{ old('status',$article->status)==='draft' ?'selected':'' }}>Save as Draft</option>
                        <option value="review" {{ old('status',$article->status)==='review'?'selected':'' }}>Submit for Review</option>
                    </select>

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Category *</label>
                    <select name="category_id" required style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.65rem 0.875rem;color:var(--text-primary);font-size:0.875rem;outline:none;margin-bottom:1rem;">
                        <option value="">Select category…</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id',$article->category_id)==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Featured Image URL</label>
                    <input type="url" name="featured_image" value="{{ old('featured_image', $article->featured_image) }}" placeholder="https://…"
                        style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.65rem 0.875rem;color:var(--text-primary);font-size:0.875rem;outline:none;box-sizing:border-box;margin-bottom:1rem;">

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Read Time (minutes)</label>
                    <input type="number" name="read_time" value="{{ old('read_time', $article->read_time ?? 5) }}" min="1"
                        style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.65rem 0.875rem;color:var(--text-primary);font-size:0.875rem;outline:none;box-sizing:border-box;margin-bottom:1.5rem;">

                    <button type="submit" class="btn-primary" style="width:100%;padding:0.8rem;font-size:0.9rem;font-weight:700;">
                        {{ $article->exists ? 'Save Changes' : 'Submit Article' }}
                    </button>
                    <a href="{{ route('contributor.articles') }}" style="display:block;width:100%;padding:0.7rem;font-size:0.875rem;font-weight:600;text-align:center;border:1px solid var(--border-subtle);border-radius:0.5rem;color:var(--text-muted);text-decoration:none;margin-top:0.75rem;box-sizing:border-box;">Cancel</a>
                </div>

                @if($article->exists)
                <div style="background:rgba(239,68,68,0.05);border:1px solid rgba(239,68,68,0.2);border-radius:1rem;padding:1.25rem;">
                    <h3 style="font-size:0.875rem;font-weight:700;margin-bottom:0.75rem;color:#f87171;">Delete Article</h3>
                    <form method="POST" action="{{ route('contributor.articles.destroy', $article) }}" onsubmit="return confirm('Permanently delete this article?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="width:100%;padding:0.6rem;font-size:0.8rem;font-weight:600;background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);border-radius:0.5rem;color:#f87171;cursor:pointer;">Delete Permanently</button>
                    </form>
                </div>
                @endif
            </div>

        </div>
    </form>

</div>
</section>

<style>
@media (max-width: 900px) {
    section > div > form > div { grid-template-columns: 1fr !important; }
}
</style>
@endsection
