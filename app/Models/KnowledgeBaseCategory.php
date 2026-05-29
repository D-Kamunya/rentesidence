<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class KnowledgeBaseCategory extends Model
{
    protected $table = 'kb_categories';
    
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'audience',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function articles(): HasMany
    {
        return $this->hasMany(KnowledgeBaseArticle::class, 'kb_category_id')
                    ->orderBy('sort_order');
    }

    public function publishedArticles(): HasMany
    {
        return $this->articles()->where('status', 'published');
    }

    // Remove the articlesForAudience method as a relationship
    // Instead, use a regular method or scope

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForAudience($query, string $audience)
    {
        return $query->where(function ($q) use ($audience) {
            $q->where('audience', $audience)
              ->orWhere('audience', 'both');
        });
    }
    
    // Add this method to get articles for a specific audience
    public function getArticlesForAudience(string $audience)
    {
        return $this->publishedArticles()
                    ->where(function ($query) use ($audience) {
                        $query->where('audience', $audience)
                              ->orWhere('audience', 'both');
                    })
                    ->latest('published_at')
                    ->get();
    }
}