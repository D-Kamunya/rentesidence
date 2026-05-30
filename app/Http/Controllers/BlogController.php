<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogComment;
use App\Models\BlogSubscriber;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::published()
            ->with(['category', 'author']);
        
        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('body', 'like', "%{$request->search}%")
                  ->orWhere('excerpt', 'like', "%{$request->search}%");
            });
        }
        
        // Category filter
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }
        
        // Tag filter
        if ($request->filled('tag')) {
            $query->whereJsonContains('tags', $request->tag);
        }
        
        $featuredPosts = BlogPost::published()
            ->featured()
            ->with(['category', 'author'])
            ->latest('published_at')
            ->limit(3)
            ->get();
            
        $posts = $query->latest('published_at')->paginate(9);
        $categories = BlogCategory::active()->withCount('posts')->orderBy('name')->get();
        $popularPosts = BlogPost::published()
            ->orderBy('views_count', 'desc')
            ->limit(5)
            ->get();
        
        return view('listing.frontend.blog.index', compact('posts', 'categories', 'featuredPosts', 'popularPosts'));
    }

    public function show($slug)
    {
        $post = BlogPost::published()
            ->where('slug', $slug)
            ->with(['category', 'author', 'comments' => function ($query) {
                $query->approved()->with(['replies', 'user']);
            }])
            ->firstOrFail();
        
        // Track view
        $post->incrementViews(
            session()->getId(),
            request()->ip()
        );
        
        // Get related posts
        $relatedPosts = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->where(function ($query) use ($post) {
                $query->where('blog_category_id', $post->blog_category_id)
                      ->orWhereJsonContains('tags', $post->tags);
            })
            ->latest('published_at')
            ->limit(3)
            ->get();
        
        // Get previous and next posts
        $previousPost = BlogPost::published()
            ->where('published_at', '<', $post->published_at)
            ->orderBy('published_at', 'desc')
            ->first();
            
        $nextPost = BlogPost::published()
            ->where('published_at', '>', $post->published_at)
            ->orderBy('published_at', 'asc')
            ->first();
        
        return view('listing.frontend.blog.show', compact('post', 'relatedPosts', 'previousPost', 'nextPost'));
    }

    public function comment(Request $request, BlogPost $post)
    {
        $validated = $request->validate([
            'author_name' => 'required|string|max:255',
            'author_email' => 'required|email|max:255',
            'content' => 'required|string|max:1000',
        ]);
        
        $validated['blog_post_id'] = $post->id;
        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';
        
        $comment = BlogComment::create($validated);
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you! Your comment has been submitted and is awaiting moderation.',
                'status' => 'pending',
            ]);
        }
        
        return redirect()
            ->back()
            ->with('comment_success', 'Thank you! Your comment has been submitted and is awaiting moderation.')
            ->with('scrollTo', 'comments-section');
    }

    public function like(Request $request, BlogPost $post)
    {
        $userId = auth()->id();
        
        $existingLike = $post->likes()->where('user_id', $userId)->first();
        
        if ($existingLike) {
            $existingLike->delete();
            $post->decrement('likes_count');
            $liked = false;
        } else {
            $post->likes()->create(['user_id' => $userId]);
            $post->increment('likes_count');
            $liked = true;
        }
        
        return response()->json([
            'likes_count' => $post->likes_count,
            'liked' => $liked,
        ]);
    }

    public function share(Request $request, BlogPost $post)
    {
        $validated = $request->validate([
            'platform' => 'required|in:facebook,twitter,linkedin,whatsapp,copy_link,email',
        ]);
        
        $post->shares()->create([
            'user_id' => auth()->id(),
            'platform' => $validated['platform'],
        ]);
        
        $post->increment('shares_count');
        
        return response()->json([
            'shares_count' => $post->shares_count,
            'success' => true,
        ]);
    }

    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:blog_subscribers,email',
            'name' => 'nullable|string|max:255',
        ]);
        
        $validated['subscribed_at'] = now();
        $validated['is_active'] = true;
        
        $subscriber = BlogSubscriber::create($validated);
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you for subscribing! You\'ll now receive updates when new articles are published.',
            ]);
        }
        
        return redirect()
            ->back()
            ->with('subscribe_success', 'Thank you for subscribing! You\'ll now receive updates when new articles are published.')
            ->with('scrollTo', 'newsletter-section');
    }

    public function unsubscribe(Request $request)
    {
        $email = $request->get('email');
        
        if (!$email) {
            return redirect()->route('blog.index')
                ->with('error', 'Invalid unsubscribe link.');
        }
        
        $subscriber = BlogSubscriber::where('email', $email)->first();
        
        if ($subscriber) {
            $subscriber->unsubscribe();
            return view('blog.unsubscribed', compact('subscriber'));
        }
        
        return redirect()->route('blog.index')
            ->with('error', 'Subscriber not found.');
    }
}