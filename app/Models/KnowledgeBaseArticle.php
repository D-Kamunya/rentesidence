<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class KnowledgeBaseArticle extends Model
{
    use SoftDeletes;

    protected $table = 'kb_articles';
    
    protected $fillable = [
        'kb_category_id',
        'created_by',
        'updated_by',
        'title',
        'slug',
        'type',
        'audience',
        'status',
        'body',
        'excerpt',
        'video_url',
        'external_url',
        'document_path',
        'document_original_name',
        'document_mime_type',
        'document_size',
        'sort_order',
        'published_at',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'views_owner' => 'integer',
        'views_affiliate' => 'integer',
        'published_at' => 'datetime',
        'document_size' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
            
            if ($article->status === 'published' && empty($article->published_at)) {
                $article->published_at = now();
            }
        });

        static::updating(function ($article) {
            if ($article->isDirty('status') && $article->status === 'published' && empty($article->published_at)) {
                $article->published_at = now();
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBaseCategory::class, 'kb_category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function views(): HasMany
    {
        return $this->hasMany(KnowledgeBaseArticleView::class, 'kb_article_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeForAudience($query, string $audience)
    {
        return $query->where(function ($q) use ($audience) {
            $q->where('audience', $audience)
              ->orWhere('audience', 'both');
        });
    }

    public function getAudienceLabelAttribute(): string
    {
        return match($this->audience) {
            'owners' => 'Owners Only',
            'affiliates' => 'Affiliates Only',
            'both' => 'Everyone',
            default => 'Unknown',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'article' => 'Article',
            'video' => 'Video',
            'document' => 'Document',
            'link' => 'External Link',
            default => 'Unknown',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'published' => 'green',
            'draft' => 'amber',
            'archived' => 'grey',
            default => 'grey',
        };
    }

    public function incrementViews(string $viewerType): void
    {
        if ($viewerType === 'owner') {
            $this->increment('views_owner');
        } else {
            $this->increment('views_affiliate');
        }
    }
}