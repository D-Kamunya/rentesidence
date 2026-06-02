<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogComment;
use App\Models\BlogSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Jobs\SendBlogNotificationJob;

class BlogController extends Controller
{
    // Dashboard with analytics
    public function dashboard()
    {
        $stats = [
            'total_posts' => BlogPost::count(),
            'published_posts' => BlogPost::where('status', 'published')->count(),
            'total_views' => BlogPost::sum('views_count'),
            'total_comments' => BlogComment::where('status', 'approved')->count(),
            'total_likes' => BlogPost::sum('likes_count'),
            'total_shares' => BlogPost::sum('shares_count'),
        ];
        
        $recentPosts = BlogPost::with(['category', 'author'])
            ->latest()
            ->limit(5)
            ->get();
            
        $popularPosts = BlogPost::published()
            ->orderBy('views_count', 'desc')
            ->limit(5)
            ->get();
            
        $pendingComments = BlogComment::where('status', 'pending')
            ->with('post')
            ->latest()
            ->limit(5)
            ->get();
        
        return view('admin.blog.dashboard', compact('stats', 'recentPosts', 'popularPosts', 'pendingComments'));
    }

    // Categories
    public function categories()
    {
        $categories = BlogCategory::withCount('posts')
            ->orderBy('sort_order')
            ->get();
            
        return view('admin.blog.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        BlogCategory::create($validated);

        return redirect()->route('admin.blog.categories')
            ->with('success', 'Category created successfully.');
    }

    public function updateCategory(Request $request, BlogCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return redirect()->route('admin.blog.categories')
            ->with('success', 'Category updated successfully.');
    }

    public function destroyCategory(BlogCategory $category)
    {
        $category->delete();

        return redirect()->route('admin.blog.categories')
            ->with('success', 'Category deleted successfully.');
    }

    // Posts
    public function posts(Request $request)
    {
        $query = BlogPost::with(['category', 'author']);
        
        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('category')) {
            $query->where('blog_category_id', $request->category);
        }
        
        $posts = $query->latest()->paginate(20);
        $categories = BlogCategory::active()->orderBy('name')->get();
        
        return view('admin.blog.posts', compact('posts', 'categories'));
    }

    public function createPost()
    {
        $categories = BlogCategory::active()->orderBy('name')->get();
        return view('admin.blog.post-form', compact('categories'));
    }

    public function storePost(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'blog_category_id' => 'nullable|exists:blog_categories,id',
            'status' => 'required|in:published,draft,scheduled',
            'body' => 'required|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|image|max:5120',
            'featured_image_alt' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'is_featured' => 'boolean',
            'tags' => 'nullable|string',
        ]);

        $validated['author_id'] = auth()->id();
        $validated['tags'] = $validated['tags'] ? array_map('trim', explode(',', $validated['tags'])) : null;
        
        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('blog-images', 'public');
            $validated['featured_image'] = $path;
        }
        
        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }
        
        $post = BlogPost::create($validated);
        
        // ✅ Only send if it's a new published post
        if ($post->status === 'published') {
            $this->notifySubscribers($post);
        }
        
        return redirect()->route('admin.blog.posts')
            ->with('success', 'Blog post created successfully.');
    }

    public function editPost(BlogPost $post)
    {
        $categories = BlogCategory::active()->orderBy('name')->get();
        return view('admin.blog.post-form', compact('post', 'categories'));
    }

    public function updatePost(Request $request, BlogPost $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'blog_category_id' => 'nullable|exists:blog_categories,id',
            'status' => 'required|in:published,draft,scheduled',
            'body' => 'required|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|image|max:5120',
            'featured_image_alt' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'is_featured' => 'boolean',
            'tags' => 'nullable|string',
        ]);

        $validated['tags'] = $validated['tags'] ? array_map('trim', explode(',', $validated['tags'])) : null;
        
        if ($request->hasFile('featured_image')) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $path = $request->file('featured_image')->store('blog-images', 'public');
            $validated['featured_image'] = $path;
        }
        
        // ✅ Track if post is being published for the first time
        $isNewlyPublished = $post->status !== 'published' && $validated['status'] === 'published';
        
        if ($validated['status'] === 'published' && !$post->published_at) {
            $validated['published_at'] = now();
        }
        
        $post->update($validated);
        
        // ✅ Only send notifications if the post is being published for the FIRST time
        if ($isNewlyPublished) {
            $this->notifySubscribers($post);
        }
        
        return redirect()->route('admin.blog.posts')
            ->with('success', 'Blog post updated successfully.');
    }

    public function destroyPost(BlogPost $post)
    {
        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }
        
        $post->delete();
        
        return redirect()->route('admin.blog.posts')
            ->with('success', 'Blog post deleted successfully.');
    }

    // Comments Management
    public function comments(Request $request)
    {
        $query = BlogComment::with(['post', 'user']);
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $comments = $query->latest()->paginate(30);
        
        return view('admin.blog.comments', compact('comments'));
    }

    public function approveComment(BlogComment $comment)
    {
        $comment->update(['status' => 'approved']);
        $comment->post->increment('comments_count');
        
        return back()->with('success', 'Comment approved successfully.');
    }

    public function markCommentAsSpam(BlogComment $comment)
    {
        $comment->update(['status' => 'spam']);
        
        return back()->with('success', 'Comment marked as spam.');
    }

    public function destroyComment(BlogComment $comment)
    {
        if ($comment->status === 'approved') {
            $comment->post->decrement('comments_count');
        }
        
        $comment->delete();
        
        return back()->with('success', 'Comment deleted successfully.');
    }

    // Subscribers Management
    public function subscribers(Request $request)
    {
        $query = BlogSubscriber::query();
        
        if ($request->filled('search')) {
            $query->where('email', 'like', "%{$request->search}%")
                  ->orWhere('name', 'like', "%{$request->search}%");
        }
        
        $subscribers = $query->latest()->paginate(30);
        
        return view('admin.blog.subscribers', compact('subscribers'));
    }

    public function destroySubscriber(BlogSubscriber $subscriber)
    {
        $subscriber->delete();
        
        return redirect()->route('admin.blog.subscribers')
            ->with('success', 'Subscriber removed successfully.');
    }

    protected function notifySubscribers(BlogPost $post)
    {
        $subscribers = BlogSubscriber::active()->get();
        
        foreach ($subscribers as $subscriber) {
            try {
                SendBlogNotificationJob::dispatch($post, $subscriber);
            } catch (\Exception $e) {
                \Log::error('Failed to dispatch blog notification job: ' . $e->getMessage());
            }
        }
    }
}