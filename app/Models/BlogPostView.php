<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPostView extends Model
{
    protected $fillable = [
        'blog_post_id',
        'ip_address',
        'user_agent',
        'session_id',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public $timestamps = false;

    public function post()
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }
}