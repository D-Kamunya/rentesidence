<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeBaseCategory;
use App\Models\KnowledgeBaseArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminKnowledgeBaseController extends Controller
{
    // Category Management
    public function categories()
    {
        $categories = KnowledgeBaseCategory::withCount('articles')
            ->orderBy('sort_order')
            ->get();
            
        return view('admin.knowledge-base.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'audience' => 'required|in:owners,affiliates,both',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        KnowledgeBaseCategory::create($validated);

        return redirect()->route('admin.kb.categories')
            ->with('success', 'Category created successfully.');
    }

    public function updateCategory(Request $request, KnowledgeBaseCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'audience' => 'required|in:owners,affiliates,both',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return redirect()->route('admin.kb.categories')
            ->with('success', 'Category updated successfully.');
    }

    public function destroyCategory(KnowledgeBaseCategory $category)
    {
        $category->delete();

        return redirect()->route('admin.kb.categories')
            ->with('success', 'Category deleted successfully.');
    }

    // Article Management
    public function articles(Request $request)
    {
        $query = KnowledgeBaseArticle::with(['category', 'creator']);
        
        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('audience')) {
            $query->where('audience', $request->audience);
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('category')) {
            $query->where('kb_category_id', $request->category);
        }
        
        $articles = $query->latest()->paginate(20);
        $categories = KnowledgeBaseCategory::active()->orderBy('name')->get();
        
        return view('admin.knowledge-base.articles', compact('articles', 'categories'));
    }

    public function createArticle()
    {
        $categories = KnowledgeBaseCategory::active()->orderBy('name')->get();
        return view('admin.knowledge-base.article-form', compact('categories'));
    }

    public function storeArticle(Request $request)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'kb_category_id' => 'nullable|exists:kb_categories,id',
            'type' => 'required|in:article,video,document,link',
            'audience' => 'required|in:owners,affiliates,both',
            'status' => 'required|in:published,draft,archived',
            'excerpt' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
        ];
        
        // Type-specific validation
        if ($request->type === 'article') {
            $rules['body'] = 'required|string';
        } elseif ($request->type === 'video') {
            $rules['video_url'] = 'required|url';
        } elseif ($request->type === 'link') {
            $rules['external_url'] = 'required|url';
        } elseif ($request->type === 'document') {
            $rules['document'] = 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip';
        }
        
        $validated = $request->validate($rules);
        $validated['created_by'] = auth()->id();
        
        // Handle document upload
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $path = $file->store('kb_documents', 'public');
            
            $validated['document_path'] = $path;
            $validated['document_original_name'] = $file->getClientOriginalName();
            $validated['document_mime_type'] = $file->getMimeType();
            $validated['document_size'] = $file->getSize();
        }
        
        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }
        
        KnowledgeBaseArticle::create($validated);
        
        return redirect()->route('admin.kb.articles')
            ->with('success', 'Article created successfully.');
    }

    public function editArticle(KnowledgeBaseArticle $article)
    {
        $categories = KnowledgeBaseCategory::active()->orderBy('name')->get();
        return view('admin.knowledge-base.article-form', compact('article', 'categories'));
    }

    public function updateArticle(Request $request, KnowledgeBaseArticle $article)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'kb_category_id' => 'nullable|exists:kb_categories,id',
            'type' => 'required|in:article,video,document,link',
            'audience' => 'required|in:owners,affiliates,both',
            'status' => 'required|in:published,draft,archived',
            'excerpt' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
        ];
        
        // Type-specific validation
        if ($request->type === 'article') {
            $rules['body'] = 'required|string';
        } elseif ($request->type === 'video') {
            $rules['video_url'] = 'required|url';
        } elseif ($request->type === 'link') {
            $rules['external_url'] = 'required|url';
        }
        
        $validated = $request->validate($rules);
        $validated['updated_by'] = auth()->id();
        
        // Handle document upload
        if ($request->hasFile('document')) {
            // Delete old document
            if ($article->document_path) {
                Storage::disk('public')->delete($article->document_path);
            }
            
            $file = $request->file('document');
            $path = $file->store('kb_documents', 'public');
            
            $validated['document_path'] = $path;
            $validated['document_original_name'] = $file->getClientOriginalName();
            $validated['document_mime_type'] = $file->getMimeType();
            $validated['document_size'] = $file->getSize();
        }
        
        if ($validated['status'] === 'published' && !$article->published_at) {
            $validated['published_at'] = now();
        }
        
        $article->update($validated);
        
        return redirect()->route('admin.kb.articles')
            ->with('success', 'Article updated successfully.');
    }

    public function destroyArticle(KnowledgeBaseArticle $article)
    {
        // Delete document if exists
        if ($article->document_path) {
            Storage::disk('public')->delete($article->document_path);
        }
        
        $article->delete();
        
        return redirect()->route('admin.kb.articles')
            ->with('success', 'Article deleted successfully.');
    }
}