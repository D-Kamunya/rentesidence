<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPostLike extends Model
{
    protected $fillable = [
        'blog_post_id',
        'user_id',
    ];

    public function post()
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}