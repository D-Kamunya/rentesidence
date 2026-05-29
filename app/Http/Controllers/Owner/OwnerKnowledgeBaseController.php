<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeBaseCategory;
use App\Models\KnowledgeBaseArticle;
use App\Models\KnowledgeBaseArticleView;
use Illuminate\Http\Request;

class OwnerKnowledgeBaseController extends Controller
{
    public function index(Request $request)
    {
        // Get categories with article count for owners
        $categories = KnowledgeBaseCategory::active()
            ->forAudience('owners')
            ->withCount(['publishedArticles as articles_count' => function ($query) {
                $query->where(function ($q) {
                    $q->where('audience', 'owners')
                      ->orWhere('audience', 'both');
                });
            }])
            ->orderBy('sort_order')
            ->get();
            
        // Get recent articles for owners
        $recentArticles = KnowledgeBaseArticle::published()
            ->forAudience('owners')
            ->with('category')
            ->latest('published_at')
            ->limit(5)
            ->get();
            
        return view('owner.knowledge-base.index', compact('categories', 'recentArticles'));
    }

    public function search(Request $request)
    {
        $search = $request->get('q');
        
        $articles = KnowledgeBaseArticle::published()
            ->forAudience('owners')
            ->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('body', 'like', "%{$search}%")
                      ->orWhere('excerpt', 'like', "%{$search}%");
            })
            ->with('category')
            ->latest('published_at')
            ->paginate(12);
            
        return view('owner.knowledge-base.search', compact('articles', 'search'));
    }

    public function category(KnowledgeBaseCategory $category)
    {
        abort_if(!$category->is_active || ($category->audience !== 'owners' && $category->audience !== 'both'), 404);
        
        $articles = $category->publishedArticles()
            ->where(function ($query) {
                $query->where('audience', 'owners')
                      ->orWhere('audience', 'both');
            })
            ->latest('published_at')
            ->paginate(12);
            
        return view('owner.knowledge-base.category', compact('category', 'articles'));
    }

    public function article(KnowledgeBaseArticle $article)
    {
        abort_if(
            $article->status !== 'published' || 
            ($article->audience !== 'owners' && $article->audience !== 'both'),
            404
        );
        
        // Track view
        $this->trackView($article, 'owner');
        
        $relatedArticles = KnowledgeBaseArticle::published()
            ->forAudience('owners')
            ->where('id', '!=', $article->id)
            ->where(function ($query) use ($article) {
                $query->where('kb_category_id', $article->kb_category_id)
                      ->orWhere('type', $article->type);
            })
            ->limit(3)
            ->get();
            
        return view('owner.knowledge-base.article', compact('article', 'relatedArticles'));
    }

    public function downloadDocument(KnowledgeBaseArticle $article)
    {
        abort_if(
            $article->type !== 'document' || 
            !$article->document_path ||
            $article->status !== 'published' ||
            ($article->audience !== 'owners' && $article->audience !== 'both'),
            404
        );
        
        // Track download
        $this->trackView($article, 'owner');
        
        return Storage::disk('public')->download(
            $article->document_path,
            $article->document_original_name
        );
    }

    protected function trackView(KnowledgeBaseArticle $article, string $viewerType)
    {
        $view = KnowledgeBaseArticleView::firstOrNew([
            'kb_article_id' => $article->id,
            'user_id' => auth()->id(),
            'viewer_type' => $viewerType,
        ]);
        
        $view->view_count = ($view->view_count ?? 0) + 1;
        $view->last_viewed_at = now();
        $view->save();
        
        $article->incrementViews($viewerType);
    }
}