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

    <form method="POST" enctype="multipart/form-data" action="{{ $article->exists ? route('contributor.articles.update', $article) : route('contributor.articles.store') }}">
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

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;font-weight:600;">Content <span style="font-weight:400;font-size:0.75rem;">(HTML supported, or upload a document)</span></label>
                    <textarea name="content" rows="24" placeholder="Write your article content here…"
                        style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.75rem 1rem;color:var(--text-primary);font-size:0.85rem;outline:none;resize:vertical;box-sizing:border-box;line-height:1.7;">{{ old('content', $article->content) }}</textarea>

                    @php
                        $oldSectionTitles = old('section_titles');
                        $existingSections = is_array($article->content_sections ?? null) ? $article->content_sections : [];
                        $sectionCount = max(is_array($oldSectionTitles) ? count($oldSectionTitles) : 0, count($existingSections), 1);
                    @endphp

                    <div style="margin-top:1.25rem;padding-top:1rem;border-top:1px dashed var(--border-subtle);">
                        <h4 style="font-size:0.85rem;color:var(--text-primary);margin:0 0 0.45rem;font-weight:700;">Section Builder</h4>
                        <p style="font-size:0.75rem;color:var(--text-muted);margin:0 0 0.9rem;">Add section text and upload up to 5 images per section.</p>

                        <div id="section-builder">
                            @for($i = 0; $i < $sectionCount; $i++)
                                @php
                                    $sec = $existingSections[$i] ?? [];
                                    $secImages = is_array($sec['images'] ?? null) ? $sec['images'] : [];
                                @endphp
                                <div class="section-row" style="padding:0.9rem;border:1px solid var(--border-subtle);border-radius:0.75rem;margin-bottom:0.75rem;background:rgba(255,255,255,0.01);">
                                    <label style="display:block;font-size:0.74rem;color:var(--text-muted);margin-bottom:0.3rem;">Section Title</label>
                                    <input type="text" name="section_titles[]" value="{{ old('section_titles.' . $i, $sec['title'] ?? '') }}"
                                        style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.45rem;padding:0.55rem 0.7rem;color:var(--text-primary);font-size:0.82rem;outline:none;box-sizing:border-box;margin-bottom:0.65rem;">

                                    <label style="display:block;font-size:0.74rem;color:var(--text-muted);margin-bottom:0.3rem;">Section Body</label>
                                    <textarea name="section_bodies[]" rows="4"
                                        style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.45rem;padding:0.55rem 0.7rem;color:var(--text-primary);font-size:0.8rem;outline:none;resize:vertical;box-sizing:border-box;margin-bottom:0.65rem;">{{ old('section_bodies.' . $i, $sec['body'] ?? '') }}</textarea>

                                    @if(!empty($secImages))
                                    <div style="display:flex;gap:0.4rem;flex-wrap:wrap;margin-bottom:0.65rem;">
                                        @foreach($secImages as $img)
                                        <img src="{{ $img }}" alt="Section image" style="width:64px;height:48px;object-fit:cover;border-radius:0.3rem;border:1px solid var(--border-subtle);">
                                        @endforeach
                                    </div>
                                    @endif

                                    <label style="display:block;font-size:0.74rem;color:var(--text-muted);margin-bottom:0.3rem;">Section Images (max 5)</label>
                                    <input type="file" name="section_image_files[{{ $i }}][]" multiple accept="image/*"
                                        style="display:block;width:100%;margin-bottom:0.65rem;color:var(--text-secondary);font-size:0.78rem;">

                                    <label style="display:block;font-size:0.74rem;color:var(--text-muted);margin-bottom:0.3rem;">Section Video URL</label>
                                    <input type="url" name="section_video_urls[]" value="{{ old('section_video_urls.' . $i, $sec['video_url'] ?? '') }}" placeholder="https://..."
                                        style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.45rem;padding:0.55rem 0.7rem;color:var(--text-primary);font-size:0.82rem;outline:none;box-sizing:border-box;margin-bottom:0.65rem;">

                                    <label style="display:block;font-size:0.74rem;color:var(--text-muted);margin-bottom:0.3rem;">Section Video File</label>
                                    <input type="file" name="section_video_files[{{ $i }}]" accept="video/mp4,video/webm,video/quicktime"
                                        style="display:block;width:100%;color:var(--text-secondary);font-size:0.78rem;">
                                </div>
                            @endfor
                        </div>

                        <button type="button" id="add-section-btn" style="padding:0.5rem 0.8rem;border:1px solid var(--border-subtle);background:rgba(34,242,226,0.08);border-radius:0.45rem;color:var(--accent-cyan);font-size:0.78rem;cursor:pointer;">+ Add Section</button>
                    </div>
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

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Import Document (DOCX/PDF/TXT/MD)</label>
                    <input type="file" name="document_file" accept=".doc,.docx,.pdf,.txt,.md"
                        style="display:block;width:100%;margin-bottom:1rem;color:var(--text-secondary);font-size:0.8rem;">

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Featured Image URL</label>
                    <input type="url" name="featured_image" value="{{ old('featured_image', $article->featured_image) }}" placeholder="https://…"
                        style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.65rem 0.875rem;color:var(--text-primary);font-size:0.875rem;outline:none;box-sizing:border-box;margin-bottom:1rem;">

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Featured Image File</label>
                    <input type="file" name="featured_image_file" accept="image/*"
                        style="display:block;width:100%;margin-bottom:1rem;color:var(--text-secondary);font-size:0.8rem;">

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Gallery Images</label>
                    <input type="file" name="gallery_image_files[]" multiple accept="image/*"
                        style="display:block;width:100%;margin-bottom:1rem;color:var(--text-secondary);font-size:0.8rem;">

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Main Video URL</label>
                    <input type="url" name="video_url" value="{{ old('video_url', $article->video_url) }}" placeholder="https://..."
                        style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.65rem 0.875rem;color:var(--text-primary);font-size:0.875rem;outline:none;box-sizing:border-box;margin-bottom:1rem;">

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Main Video File</label>
                    <input type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime"
                        style="display:block;width:100%;margin-bottom:1rem;color:var(--text-secondary);font-size:0.8rem;">

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

<script>
(function() {
    const addBtn = document.getElementById('add-section-btn');
    const container = document.getElementById('section-builder');
    if (!addBtn || !container) return;

    addBtn.addEventListener('click', function () {
        const idx = container.querySelectorAll('.section-row').length;
        const row = document.createElement('div');
        row.className = 'section-row';
        row.style.cssText = 'padding:0.9rem;border:1px solid var(--border-subtle);border-radius:0.75rem;margin-bottom:0.75rem;background:rgba(255,255,255,0.01);';
        row.innerHTML = `
            <label style="display:block;font-size:0.74rem;color:var(--text-muted);margin-bottom:0.3rem;">Section Title</label>
            <input type="text" name="section_titles[]" style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.45rem;padding:0.55rem 0.7rem;color:var(--text-primary);font-size:0.82rem;outline:none;box-sizing:border-box;margin-bottom:0.65rem;">
            <label style="display:block;font-size:0.74rem;color:var(--text-muted);margin-bottom:0.3rem;">Section Body</label>
            <textarea name="section_bodies[]" rows="4" style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.45rem;padding:0.55rem 0.7rem;color:var(--text-primary);font-size:0.8rem;outline:none;resize:vertical;box-sizing:border-box;margin-bottom:0.65rem;"></textarea>
            <label style="display:block;font-size:0.74rem;color:var(--text-muted);margin-bottom:0.3rem;">Section Images (max 5)</label>
            <input type="file" name="section_image_files[${idx}][]" multiple accept="image/*" style="display:block;width:100%;margin-bottom:0.65rem;color:var(--text-secondary);font-size:0.78rem;">
            <label style="display:block;font-size:0.74rem;color:var(--text-muted);margin-bottom:0.3rem;">Section Video URL</label>
            <input type="url" name="section_video_urls[]" placeholder="https://..." style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.45rem;padding:0.55rem 0.7rem;color:var(--text-primary);font-size:0.82rem;outline:none;box-sizing:border-box;margin-bottom:0.65rem;">
            <label style="display:block;font-size:0.74rem;color:var(--text-muted);margin-bottom:0.3rem;">Section Video File</label>
            <input type="file" name="section_video_files[${idx}]" accept="video/mp4,video/webm,video/quicktime" style="display:block;width:100%;color:var(--text-secondary);font-size:0.78rem;">
        `;
        container.appendChild(row);
    });
})();
</script>

<style>
@media (max-width: 900px) {
    section > div > form > div { grid-template-columns: 1fr !important; }
}
</style>
@endsection
