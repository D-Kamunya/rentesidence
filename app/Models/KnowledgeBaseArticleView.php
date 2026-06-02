<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeBaseArticleView extends Model
{
    protected $table = 'kb_article_views';
    
    protected $fillable = [
        'kb_article_id',
        'user_id',
        'viewer_type',
        'last_viewed_at',
        'view_count',
    ];

    protected $casts = [
        'last_viewed_at' => 'datetime',
        'view_count' => 'integer',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBaseArticle::class, 'kb_article_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}