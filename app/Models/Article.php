<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Article extends Model
{
    protected $fillable = [
        'title', 'slug', 'summary', 'content', 'featured_image',
        'category_id', 'author_id', 'status', 'read_time', 'views',
        'quick_facts', 'images', 'content_sections', 'meta',
        'meta_title', 'meta_description', 'published_at', 'video_url',
    ];

    protected $casts = [
        'quick_facts' => 'array',
        'images' => 'array',
        'content_sections' => 'array',
        'meta' => 'array',
        'published_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function relatedArticles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_article', 'article_id', 'related_article_id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function bookmarks(): MorphMany
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }
}
