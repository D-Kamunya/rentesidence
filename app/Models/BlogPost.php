<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'blog_category_id',
        'author_id',
        'title',
        'slug',
        'status',
        'body',
        'excerpt',
        'featured_image',
        'featured_image_alt',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'views_count',
        'likes_count',
        'shares_count',
        'comments_count',
        'reading_time',
        'is_featured',
        'tags',
        'published_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'reading_time' => 'integer',
        'views_count' => 'integer',
        'likes_count' => 'integer',
        'shares_count' => 'integer',
        'comments_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
            
            if (empty($post->excerpt)) {
                $post->excerpt = Str::limit(strip_tags($post->body), 200);
            }
            
            // Calculate reading time
            if (empty($post->reading_time) && !empty($post->body)) {
                $wordCount = str_word_count(strip_tags($post->body));
                $post->reading_time = max(1, ceil($wordCount / 200)); // Average 200 words per minute
            }
        });

        static::updating(function ($post) {
            if ($post->isDirty('body')) {
                $wordCount = str_word_count(strip_tags($post->body));
                $post->reading_time = max(1, ceil($wordCount / 200));
            }
        });
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function comments()
    {
        return $this->hasMany(BlogComment::class, 'blog_post_id')
                    ->whereNull('parent_id')
                    ->where('status', 'approved')
                    ->orderBy('created_at', 'desc');
    }

    public function allComments()
    {
        return $this->hasMany(BlogComment::class, 'blog_post_id');
    }

    public function likes()
    {
        return $this->hasMany(BlogPostLike::class, 'blog_post_id');
    }

    public function shares()
    {
        return $this->hasMany(BlogPostShare::class, 'blog_post_id');
    }

    public function views()
    {
        return $this->hasMany(BlogPostView::class, 'blog_post_id');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('published_at', '>', now());
    }

    // Accessors
    public function getReadingTimeTextAttribute()
    {
        return ($this->reading_time ?? 1) . ' min read';
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'published' => 'green',
            'draft' => 'amber',
            'scheduled' => 'blue',
            default => 'grey',
        };
    }

    public function getShareUrlAttribute()
    {
        return route('blog.show', $this->slug);
    }

    public function getSocialShareLinksAttribute()
    {
        $url = urlencode($this->share_url);
        $title = urlencode($this->title);
        
        return [
            'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$url}",
            'twitter' => "https://twitter.com/intent/tweet?url={$url}&text={$title}",
            'linkedin' => "https://www.linkedin.com/sharing/share-offsite/?url={$url}",
            'whatsapp' => "https://api.whatsapp.com/send?text={$title}%20{$url}",
            'email' => "mailto:?subject={$title}&body={$url}",
            'copy_link' => $this->share_url,
        ];
    }

    // Helper Methods
    public function incrementViews($sessionId = null, $ipAddress = null)
    {
        $this->increment('views_count');
        
        if ($sessionId || $ipAddress) {
            BlogPostView::create([
                'blog_post_id' => $this->id,
                'session_id' => $sessionId,
                'ip_address' => $ipAddress,
                'user_agent' => request()->userAgent(),
                'viewed_at' => now(),
            ]);
        }
    }

    public function isLikedByUser($userId = null)
    {
        if (!$userId) return false;
        
        return $this->likes()->where('user_id', $userId)->exists();
    }

    public function toggleLike($userId)
    {
        $existingLike = $this->likes()->where('user_id', $userId)->first();
        
        if ($existingLike) {
            $existingLike->delete();
            $this->decrement('likes_count');
            return false;
        } else {
            $this->likes()->create(['user_id' => $userId]);
            $this->increment('likes_count');
            return true;
        }
    }

    public function recordShare($userId, $platform)
    {
        $this->shares()->create([
            'user_id' => $userId,
            'platform' => $platform,
        ]);
        
        $this->increment('shares_count');
    }

    public function getRelatedPosts($limit = 3)
    {
        return self::published()
            ->where('id', '!=', $this->id)
            ->where(function ($query) {
                $query->where('blog_category_id', $this->blog_category_id)
                      ->orWhereJsonContains('tags', $this->tags);
            })
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    public function getPreviousPost()
    {
        return self::published()
            ->where('published_at', '<', $this->published_at)
            ->orderBy('published_at', 'desc')
            ->first();
    }

    public function getNextPost()
    {
        return self::published()
            ->where('published_at', '>', $this->published_at)
            ->orderBy('published_at', 'asc')
            ->first();
    }
}