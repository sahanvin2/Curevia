<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'icon', 'color', 'sort_order'];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }
}
