<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $story->exists ? 'Edit Story' : 'New Story' }} | Curevia Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background:#0B0F14;color:#F0F4FF;font-family:'Inter',system-ui,sans-serif;">

@include('admin.layouts.sidebar')

<main style="margin-left:260px;padding:2rem;max-width:1200px;" id="admin-main">
    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:2rem;flex-wrap:wrap;">
        <a href="{{ route('admin.stories.index') }}" style="color:var(--text-muted);text-decoration:none;font-size:0.85rem;">← Stories</a>
        <span style="color:var(--border-subtle);">/</span>
        <h1 style="font-size:1.4rem;font-weight:800;">{{ $story->exists ? 'Edit Story' : 'New Story' }}</h1>
    </div>

    @if($errors->any())
    <div style="background:rgba(239,68,68,0.1);border:1px solid #f87171;color:#f87171;padding:1rem;border-radius:0.75rem;margin-bottom:1.5rem;">
        <ul style="margin:0;padding-left:1.25rem;">
            @foreach($errors->all() as $error)<li style="font-size:0.875rem;">{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" enctype="multipart/form-data" action="{{ $story->exists ? route('admin.stories.update', $story) : route('admin.stories.store') }}" id="story-form">
        @csrf
        @if($story->exists) @method('PUT') @endif

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
            <div style="display:flex;flex-direction:column;gap:1.25rem;">
                <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;padding:1.5rem;">
                    <h3 style="font-size:0.875rem;font-weight:700;margin-bottom:1rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;">Content</h3>

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Title *</label>
                    <input type="text" name="title" value="{{ old('title', $story->title) }}" required style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.65rem 0.875rem;color:var(--text-primary);font-size:0.875rem;outline:none;box-sizing:border-box;margin-bottom:1rem;">

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Excerpt *</label>
                    <textarea name="excerpt" required rows="3" style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.65rem 0.875rem;color:var(--text-primary);font-size:0.875rem;outline:none;resize:vertical;box-sizing:border-box;margin-bottom:1rem;">{{ old('excerpt', $story->excerpt) }}</textarea>

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Content (HTML or import document)</label>
                    <textarea name="content" rows="18" style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.65rem 0.875rem;color:var(--text-primary);font-size:0.8rem;outline:none;resize:vertical;box-sizing:border-box;font-family:monospace;">{{ old('content', $story->content) }}</textarea>

                    @php
                        $oldSectionTitles = old('section_titles');
                        $existingSections = is_array($story->content_sections ?? null) ? $story->content_sections : [];
                        $sectionCount = max(is_array($oldSectionTitles) ? count($oldSectionTitles) : 0, count($existingSections), 1);
                    @endphp

                    <div style="margin-top:1.25rem;padding-top:1.1rem;border-top:1px dashed var(--border-subtle);">
                        <h4 style="font-size:0.85rem;font-weight:700;color:var(--text-primary);margin:0 0 0.4rem;">Section Builder</h4>
                        <p style="font-size:0.75rem;color:var(--text-muted);margin:0 0 0.9rem;">Drag sections to reorder. You can remove or replace section media.</p>

                        <div id="section-builder">
                            @for($i = 0; $i < $sectionCount; $i++)
                                @php
                                    $sec = $existingSections[$i] ?? [];
                                    $secImages = is_array($sec['images'] ?? null) ? $sec['images'] : [];
                                @endphp
                                <div class="section-row" draggable="true" style="padding:0.9rem;border:1px solid var(--border-subtle);border-radius:0.75rem;margin-bottom:0.75rem;background:rgba(255,255,255,0.01);" data-index="{{ $i }}">
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                                        <span class="section-label" style="font-size:0.72rem;color:var(--text-muted);">Section {{ $i + 1 }}</span>
                                        <span class="drag-handle" style="cursor:grab;font-size:0.72rem;color:var(--accent-cyan);">Drag</span>
                                    </div>

                                    <label style="display:block;font-size:0.74rem;color:var(--text-muted);margin-bottom:0.3rem;">Section Title</label>
                                    <input type="text" name="section_titles[]" value="{{ old('section_titles.' . $i, $sec['title'] ?? '') }}" style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.45rem;padding:0.55rem 0.7rem;color:var(--text-primary);font-size:0.82rem;outline:none;box-sizing:border-box;margin-bottom:0.65rem;">

                                    <label style="display:block;font-size:0.74rem;color:var(--text-muted);margin-bottom:0.3rem;">Section Body</label>
                                    <textarea name="section_bodies[]" rows="4" style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.45rem;padding:0.55rem 0.7rem;color:var(--text-primary);font-size:0.8rem;outline:none;resize:vertical;box-sizing:border-box;margin-bottom:0.65rem;">{{ old('section_bodies.' . $i, $sec['body'] ?? '') }}</textarea>

                                    @if(!empty($secImages))
                                    <div style="display:flex;gap:0.4rem;flex-wrap:wrap;margin-bottom:0.65rem;">
                                        @foreach($secImages as $img)
                                        <label style="display:block;position:relative;">
                                            <img src="{{ $img }}" alt="Section image" style="width:64px;height:48px;object-fit:cover;border-radius:0.3rem;border:1px solid var(--border-subtle);display:block;">
                                            <span style="font-size:0.62rem;color:#f87171;display:block;margin-top:0.2rem;">Remove</span>
                                            <input type="checkbox" name="remove_section_images[{{ $i }}][]" value="{{ $img }}">
                                        </label>
                                        @endforeach
                                    </div>
                                    @endif

                                    <label style="display:block;font-size:0.74rem;color:var(--text-muted);margin-bottom:0.3rem;">Section Images (max 5)</label>
                                    <input type="file" name="section_image_files[{{ $i }}][]" multiple accept="image/*" style="display:block;width:100%;margin-bottom:0.65rem;color:var(--text-secondary);font-size:0.78rem;">

                                    <label style="display:block;font-size:0.74rem;color:var(--text-muted);margin-bottom:0.3rem;">Section Video URL</label>
                                    <input type="url" name="section_video_urls[]" value="{{ old('section_video_urls.' . $i, $sec['video_url'] ?? '') }}" placeholder="https://..." style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.45rem;padding:0.55rem 0.7rem;color:var(--text-primary);font-size:0.82rem;outline:none;box-sizing:border-box;margin-bottom:0.65rem;">

                                    @if(!empty($sec['video_url']))
                                    <label style="display:flex;gap:0.4rem;align-items:center;font-size:0.72rem;color:#f87171;margin-bottom:0.5rem;">
                                        <input type="checkbox" name="remove_section_video[{{ $i }}]" value="1"> Remove existing section video
                                    </label>
                                    @endif

                                    <label style="display:block;font-size:0.74rem;color:var(--text-muted);margin-bottom:0.3rem;">Section Video File</label>
                                    <input type="file" name="section_video_files[{{ $i }}]" accept="video/mp4,video/webm,video/quicktime" style="display:block;width:100%;color:var(--text-secondary);font-size:0.78rem;">
                                </div>
                            @endfor
                        </div>

                        <button type="button" id="add-section-btn" style="padding:0.5rem 0.8rem;border:1px solid var(--border-subtle);background:rgba(34,242,226,0.08);border-radius:0.45rem;color:var(--accent-cyan);font-size:0.78rem;cursor:pointer;">+ Add Section</button>
                    </div>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:1.25rem;">
                <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;padding:1.5rem;">
                    <h3 style="font-size:0.875rem;font-weight:700;margin-bottom:1rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;">Settings</h3>

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Status *</label>
                    <select name="status" required style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.65rem 0.875rem;color:var(--text-primary);font-size:0.875rem;outline:none;margin-bottom:1rem;">
                        <option value="draft" {{ old('status',$story->status)==='draft'?'selected':'' }}>Draft</option>
                        <option value="review" {{ old('status',$story->status)==='review'?'selected':'' }}>In Review</option>
                        <option value="published" {{ old('status',$story->status)==='published'?'selected':'' }}>Published</option>
                    </select>

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Category *</label>
                    <select name="category_id" required style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.65rem 0.875rem;color:var(--text-primary);font-size:0.875rem;outline:none;margin-bottom:1rem;">
                        <option value="">Select category...</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id',$story->category_id)==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Author *</label>
                    <select name="author_id" required style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.65rem 0.875rem;color:var(--text-primary);font-size:0.875rem;outline:none;margin-bottom:1rem;">
                        @foreach($contributors as $contrib)
                        <option value="{{ $contrib->id }}" {{ old('author_id',$story->author_id)==$contrib->id?'selected':'' }}>{{ $contrib->name }}</option>
                        @endforeach
                    </select>

                    <label style="display:flex;align-items:center;gap:0.45rem;font-size:0.8rem;color:var(--text-muted);margin-bottom:1rem;">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $story->is_featured) ? 'checked' : '' }}> Mark as featured story
                    </label>

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Import Document (DOCX/PDF/TXT/MD)</label>
                    <input type="file" name="document_file" accept=".doc,.docx,.pdf,.txt,.md" style="display:block;width:100%;margin-bottom:1rem;color:var(--text-secondary);font-size:0.8rem;">

                    @php $galleryImages = old('existing_gallery_images', is_array($story->images ?? null) ? $story->images : []); @endphp

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Featured Image URL</label>
                    <input type="url" name="featured_image" value="{{ old('featured_image', $story->featured_image) }}" placeholder="https://..." style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.65rem 0.875rem;color:var(--text-primary);font-size:0.875rem;outline:none;box-sizing:border-box;margin-bottom:0.75rem;">

                    <div id="featured-dropzone" style="border:1px dashed var(--border-subtle);border-radius:0.65rem;padding:0.75rem 0.85rem;background:rgba(34,242,226,0.04);margin-bottom:0.75rem;cursor:pointer;">
                        <div style="font-size:0.78rem;color:var(--text-secondary);font-weight:600;">Drop featured image here</div>
                        <div style="font-size:0.72rem;color:var(--text-muted);margin-top:0.15rem;">or click to upload directly to B2 storage</div>
                    </div>

                    <div id="featured-preview-inline" style="display:none;align-items:center;gap:0.75rem;margin-bottom:0.75rem;padding:0.6rem;border:1px solid var(--border-subtle);border-radius:0.65rem;background:rgba(255,255,255,0.02);">
                        <img id="featured-preview-img" src="" alt="Featured image preview" style="width:72px;height:54px;object-fit:cover;border-radius:0.45rem;border:1px solid var(--border-subtle);">
                        <div style="font-size:0.75rem;color:var(--text-secondary);">Uploaded to B2 and linked to this story.</div>
                    </div>

                    @if($story->featured_image)
                    <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;padding:0.6rem;border:1px solid var(--border-subtle);border-radius:0.65rem;background:rgba(255,255,255,0.02);">
                        <img src="{{ $story->featured_image }}" alt="Featured image" style="width:72px;height:54px;object-fit:cover;border-radius:0.45rem;border:1px solid var(--border-subtle);">
                        <label style="display:flex;align-items:center;gap:0.45rem;font-size:0.78rem;color:#f87171;">
                            <input type="checkbox" name="remove_featured_image" value="1"> Remove current featured image
                        </label>
                    </div>
                    @endif

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Featured Image File</label>
                    <input type="file" name="featured_image_file" accept="image/*" style="display:block;width:100%;margin-bottom:1rem;color:var(--text-secondary);font-size:0.8rem;">

                    @if(!empty($galleryImages))
                    <div style="margin-bottom:1rem;">
                        <input type="hidden" name="gallery_state_submitted" value="1">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:0.75rem;margin-bottom:0.45rem;">
                            <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin:0;">Existing Gallery</label>
                            <span style="font-size:0.72rem;color:var(--text-muted);">Drag to reorder</span>
                        </div>
                        <div id="existing-gallery" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0.65rem;">
                            @foreach($galleryImages as $img)
                            <div class="gallery-item" draggable="true" style="position:relative;border:1px solid var(--border-subtle);border-radius:0.7rem;overflow:hidden;background:rgba(255,255,255,0.02);">
                                <input type="hidden" name="existing_gallery_images[]" value="{{ $img }}">
                                <img src="{{ $img }}" alt="Gallery image" style="width:100%;height:110px;object-fit:cover;display:block;">
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:0.5rem;padding:0.45rem 0.55rem;">
                                    <span class="drag-handle" style="font-size:0.72rem;color:var(--accent-cyan);cursor:grab;">Drag</span>
                                    <button type="button" class="remove-gallery-btn" style="background:none;border:none;color:#f87171;font-size:0.72rem;cursor:pointer;padding:0;">Remove</button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div id="gallery-dropzone" style="border:1px dashed var(--border-subtle);border-radius:0.65rem;padding:0.85rem;background:rgba(34,242,226,0.04);margin-bottom:0.75rem;cursor:pointer;">
                        <div style="font-size:0.78rem;color:var(--text-secondary);font-weight:600;">Drop gallery images here</div>
                        <div style="font-size:0.72rem;color:var(--text-muted);margin-top:0.15rem;">or click to upload multiple images directly to B2</div>
                    </div>

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Gallery Images</label>
                    <input type="file" name="gallery_image_files[]" multiple accept="image/*" style="display:block;width:100%;margin-bottom:1rem;color:var(--text-secondary);font-size:0.8rem;">

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Main Video URL</label>
                    <input type="url" name="video_url" value="{{ old('video_url', $story->video_url) }}" placeholder="https://..." style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.65rem 0.875rem;color:var(--text-primary);font-size:0.875rem;outline:none;box-sizing:border-box;margin-bottom:0.75rem;">

                    @if($story->video_url)
                    <label style="display:flex;align-items:center;gap:0.45rem;font-size:0.78rem;color:#f87171;margin-bottom:0.75rem;">
                        <input type="checkbox" name="remove_video" value="1"> Remove current main video
                    </label>
                    @endif

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Main Video File</label>
                    <input type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime" style="display:block;width:100%;margin-bottom:1rem;color:var(--text-secondary);font-size:0.8rem;">

                    <label style="display:block;font-size:0.8rem;color:var(--text-muted);margin-bottom:0.3rem;">Read Time (mins)</label>
                    <input type="number" name="read_time" value="{{ old('read_time', $story->read_time ?? 8) }}" min="1" style="width:100%;background:rgba(0,0,0,0.3);border:1px solid var(--border-subtle);border-radius:0.5rem;padding:0.65rem 0.875rem;color:var(--text-primary);font-size:0.875rem;outline:none;box-sizing:border-box;margin-bottom:1.5rem;">

                    <button type="submit" class="btn-primary" style="width:100%;padding:0.7rem;font-size:0.875rem;font-weight:700;">{{ $story->exists ? 'Save Changes' : 'Create Story' }}</button>
                </div>
            </div>
        </div>
    </form>
</main>

<script>
(function() {
    const form = document.getElementById('story-form');
    const addBtn = document.getElementById('add-section-btn');
    const container = document.getElementById('section-builder');
    const galleryContainer = document.getElementById('existing-gallery');
    const featuredInput = form ? form.querySelector('input[name="featured_image"]') : null;
    const featuredDropzone = document.getElementById('featured-dropzone');
    const galleryDropzone = document.getElementById('gallery-dropzone');
    const featuredPreviewWrap = document.getElementById('featured-preview-inline');
    const featuredPreviewImg = document.getElementById('featured-preview-img');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || form?.querySelector('input[name="_token"]')?.value;
    if (!form || !addBtn || !container) return;

    let dragEl = null;
    let dragGalleryEl = null;

    async function uploadImageToB2(file, folder) {
        if (!file || !csrfToken) return null;

        const payload = new FormData();
        payload.append('file', file);
        payload.append('folder', folder);

        const response = await fetch('/api/media/upload', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: payload,
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error('Upload failed');
        }

        const data = await response.json();
        return data.url || null;
    }

    function createGalleryItem(url) {
        if (!galleryContainer || !url) return;

        const item = document.createElement('div');
        item.className = 'gallery-item';
        item.draggable = true;
        item.style.cssText = 'position:relative;border:1px solid var(--border-subtle);border-radius:0.7rem;overflow:hidden;background:rgba(255,255,255,0.02);';
        item.innerHTML = `
            <input type="hidden" name="existing_gallery_images[]" value="${url}">
            <img src="${url}" alt="Gallery image" style="width:100%;height:110px;object-fit:cover;display:block;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:0.5rem;padding:0.45rem 0.55rem;">
                <span class="drag-handle" style="font-size:0.72rem;color:var(--accent-cyan);cursor:grab;">Drag</span>
                <button type="button" class="remove-gallery-btn" style="background:none;border:none;color:#f87171;font-size:0.72rem;cursor:pointer;padding:0;">Remove</button>
            </div>
        `;
        galleryContainer.appendChild(item);
        wireGalleryItem(item);
    }

    function setFeaturedPreview(url) {
        if (!featuredPreviewWrap || !featuredPreviewImg || !url) return;
        featuredPreviewImg.src = url;
        featuredPreviewWrap.style.display = 'flex';
    }

    async function handleFeaturedUpload(files) {
        const [file] = Array.from(files || []);
        if (!file) return;

        try {
            const url = await uploadImageToB2(file, 'stories/featured');
            if (!url) return;
            if (featuredInput) {
                featuredInput.value = url;
            }
            setFeaturedPreview(url);
        } catch (error) {
            alert('Featured image upload failed. Please try again.');
        }
    }

    async function handleGalleryUpload(files) {
        const entries = Array.from(files || []).slice(0, 40);
        if (entries.length === 0) return;

        for (const file of entries) {
            try {
                const url = await uploadImageToB2(file, 'stories/gallery');
                if (url) {
                    createGalleryItem(url);
                }
            } catch (error) {
                alert('One of the gallery uploads failed.');
                break;
            }
        }
    }

    function refreshSectionIndexes() {
        const rows = container.querySelectorAll('.section-row');
        rows.forEach((row, idx) => {
            row.dataset.index = String(idx);
            const label = row.querySelector('.section-label');
            if (label) label.textContent = 'Section ' + (idx + 1);

            row.querySelectorAll('input[type="file"]').forEach((input) => {
                if (input.name.startsWith('section_image_files[')) {
                    input.name = 'section_image_files[' + idx + '][]';
                }
                if (input.name.startsWith('section_video_files[')) {
                    input.name = 'section_video_files[' + idx + ']';
                }
            });

            row.querySelectorAll('input[name^="remove_section_images["]').forEach((input) => {
                input.name = 'remove_section_images[' + idx + '][]';
            });
            row.querySelectorAll('input[name^="remove_section_video["]').forEach((input) => {
                input.name = 'remove_section_video[' + idx + ']';
            });
        });
    }

    function wireSectionDrag(row) {
        row.addEventListener('dragstart', () => {
            dragEl = row;
            row.style.opacity = '0.5';
        });
        row.addEventListener('dragend', () => {
            row.style.opacity = '1';
            dragEl = null;
            refreshSectionIndexes();
        });
        row.addEventListener('dragover', (e) => {
            e.preventDefault();
        });
        row.addEventListener('drop', (e) => {
            e.preventDefault();
            if (!dragEl || dragEl === row) return;
            const rows = Array.from(container.querySelectorAll('.section-row'));
            const dragIndex = rows.indexOf(dragEl);
            const dropIndex = rows.indexOf(row);
            if (dragIndex < dropIndex) {
                row.after(dragEl);
            } else {
                row.before(dragEl);
            }
            refreshSectionIndexes();
        });
    }

    function wireGalleryItem(item) {
        item.addEventListener('dragstart', () => {
            dragGalleryEl = item;
            item.style.opacity = '0.5';
        });
        item.addEventListener('dragend', () => {
            item.style.opacity = '1';
            dragGalleryEl = null;
        });
        item.addEventListener('dragover', (e) => e.preventDefault());
        item.addEventListener('drop', (e) => {
            e.preventDefault();
            if (!dragGalleryEl || dragGalleryEl === item || !galleryContainer) return;
            const items = Array.from(galleryContainer.querySelectorAll('.gallery-item'));
            const dragIndex = items.indexOf(dragGalleryEl);
            const dropIndex = items.indexOf(item);
            if (dragIndex < dropIndex) item.after(dragGalleryEl);
            else item.before(dragGalleryEl);
        });
        const removeBtn = item.querySelector('.remove-gallery-btn');
        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                const hidden = item.querySelector('input[name="existing_gallery_images[]"]');
                if (hidden && featuredInput && featuredInput.value === hidden.value) {
                    featuredInput.value = '';
                }
                item.remove();
            });
        }
    }

    container.querySelectorAll('.section-row').forEach(wireSectionDrag);
    if (galleryContainer) {
        galleryContainer.querySelectorAll('.gallery-item').forEach(wireGalleryItem);
    }

    if (featuredInput && featuredInput.value) {
        setFeaturedPreview(featuredInput.value);
    }

    if (featuredDropzone) {
        featuredDropzone.addEventListener('click', () => {
            const picker = document.createElement('input');
            picker.type = 'file';
            picker.accept = 'image/*';
            picker.onchange = () => handleFeaturedUpload(picker.files);
            picker.click();
        });

        featuredDropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            featuredDropzone.style.borderColor = 'var(--accent-cyan)';
        });

        featuredDropzone.addEventListener('dragleave', () => {
            featuredDropzone.style.borderColor = 'var(--border-subtle)';
        });

        featuredDropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            featuredDropzone.style.borderColor = 'var(--border-subtle)';
            handleFeaturedUpload(e.dataTransfer.files);
        });
    }

    if (galleryDropzone) {
        galleryDropzone.addEventListener('click', () => {
            const picker = document.createElement('input');
            picker.type = 'file';
            picker.accept = 'image/*';
            picker.multiple = true;
            picker.onchange = () => handleGalleryUpload(picker.files);
            picker.click();
        });

        galleryDropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            galleryDropzone.style.borderColor = 'var(--accent-cyan)';
        });

        galleryDropzone.addEventListener('dragleave', () => {
            galleryDropzone.style.borderColor = 'var(--border-subtle)';
        });

        galleryDropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            galleryDropzone.style.borderColor = 'var(--border-subtle)';
            handleGalleryUpload(e.dataTransfer.files);
        });
    }

    addBtn.addEventListener('click', function() {
        const idx = container.querySelectorAll('.section-row').length;
        const row = document.createElement('div');
        row.className = 'section-row';
        row.draggable = true;
        row.dataset.index = String(idx);
        row.style.cssText = 'padding:0.9rem;border:1px solid var(--border-subtle);border-radius:0.75rem;margin-bottom:0.75rem;background:rgba(255,255,255,0.01);';
        row.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                <span class="section-label" style="font-size:0.72rem;color:var(--text-muted);">Section ${idx + 1}</span>
                <span class="drag-handle" style="cursor:grab;font-size:0.72rem;color:var(--accent-cyan);">Drag</span>
            </div>
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

        wireSectionDrag(row);
        container.appendChild(row);
        refreshSectionIndexes();
    });

    form.addEventListener('submit', refreshSectionIndexes);
})();
</script>

<style>
@media (max-width: 1024px) {
    #admin-sidebar { transform: translateX(-100%); position: fixed; top:0; left:0; bottom:0; z-index:1000; transition: transform 0.3s ease; }
    #admin-sidebar.open { transform: translateX(0); }
    #admin-main { margin-left: 0 !important; }
    #admin-main > form > div { grid-template-columns: 1fr !important; }
}
@media (max-width: 640px) {
    #admin-main { padding: 1rem !important; }
}
</style>

</body>
</html>
