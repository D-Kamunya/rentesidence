<?php

namespace App\Http\Controllers\FinancePartner;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeBaseArticle;
use App\Models\KnowledgeBaseArticleView;
use App\Models\KnowledgeBaseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Finance-partner knowledge base — admin-authored guides (how to set up a
 * facility, interest models, settlement, getting started) so new partners
 * self-onboard. Mirrors the affiliate consumer, scoped strictly to the
 * `finance_partners` audience.
 */
class KnowledgeBaseController extends Controller
{
    private const AUDIENCE = 'finance_partners';

    public function index()
    {
        $categories = KnowledgeBaseCategory::active()
            ->where('audience', self::AUDIENCE)
            ->withCount(['publishedArticles as articles_count' => fn ($q) => $q->where('audience', self::AUDIENCE)])
            ->orderBy('sort_order')
            ->get();

        $recentArticles = KnowledgeBaseArticle::published()
            ->where('audience', self::AUDIENCE)
            ->with('category')
            ->latest('published_at')
            ->limit(6)
            ->get();

        return view('finance-partner.knowledge-base.index', [
            'pageTitle' => 'Knowledge base',
            'categories' => $categories,
            'recentArticles' => $recentArticles,
        ]);
    }

    public function search(Request $request)
    {
        $search = (string) $request->get('q');

        $articles = KnowledgeBaseArticle::published()
            ->where('audience', self::AUDIENCE)
            ->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('body', 'like', "%{$search}%")
                      ->orWhere('excerpt', 'like', "%{$search}%");
            })
            ->with('category')
            ->latest('published_at')
            ->paginate(12);

        return view('finance-partner.knowledge-base.search', [
            'pageTitle' => 'Search', 'articles' => $articles, 'search' => $search,
        ]);
    }

    public function category(KnowledgeBaseCategory $category)
    {
        abort_if(! $category->is_active || $category->audience !== self::AUDIENCE, 404);

        $articles = $category->publishedArticles()
            ->where('audience', self::AUDIENCE)
            ->latest('published_at')
            ->paginate(12);

        return view('finance-partner.knowledge-base.category', [
            'pageTitle' => $category->name, 'category' => $category, 'articles' => $articles,
        ]);
    }

    public function article(KnowledgeBaseArticle $article)
    {
        abort_if($article->status !== 'published' || $article->audience !== self::AUDIENCE, 404);

        $this->trackView($article);

        $relatedArticles = KnowledgeBaseArticle::published()
            ->where('audience', self::AUDIENCE)
            ->where('id', '!=', $article->id)
            ->where(fn ($q) => $q->where('kb_category_id', $article->kb_category_id)->orWhere('type', $article->type))
            ->limit(3)
            ->get();

        return view('finance-partner.knowledge-base.article', [
            'pageTitle' => $article->title, 'article' => $article, 'relatedArticles' => $relatedArticles,
        ]);
    }

    public function downloadDocument(KnowledgeBaseArticle $article)
    {
        abort_if(
            $article->type !== 'document' || ! $article->document_path
            || $article->status !== 'published' || $article->audience !== self::AUDIENCE,
            404
        );

        $this->trackView($article);

        return Storage::disk('public')->download($article->document_path, $article->document_original_name);
    }

    private function trackView(KnowledgeBaseArticle $article): void
    {
        $view = KnowledgeBaseArticleView::firstOrNew([
            'kb_article_id' => $article->id,
            'user_id' => auth()->id(),
            'viewer_type' => 'finance_partner',
        ]);
        $view->view_count = ($view->view_count ?? 0) + 1;
        $view->last_viewed_at = now();
        $view->save();

        $article->incrementViews('finance_partner');
    }
}
