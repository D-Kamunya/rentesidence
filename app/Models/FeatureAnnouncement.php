<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeatureAnnouncement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'body', 'icon', 'link_url', 'link_label', 'kb_article_id', 'audience', 'is_active', 'published_at',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'published_at' => 'datetime',
    ];

    /** Users who have seen (dismissed) this announcement. */
    public function seenBy()
    {
        return $this->belongsToMany(User::class, 'feature_announcement_user')->withPivot('seen_at');
    }

    public function kbArticle()
    {
        return $this->belongsTo(KnowledgeBaseArticle::class, 'kb_article_id');
    }

    /**
     * The "Learn more" link resolved for a viewer's role. A linked KB article wins — its view route
     * is per-role, so we build the right one for whoever's looking; otherwise the free-form link_url.
     * Returns null when neither applies (e.g. a KB link but the viewer's role has no KB surface).
     */
    public function resolvedLink(int $role): ?string
    {
        if ($this->kb_article_id && ($article = $this->kbArticle)) {
            $routeByRole = [
                USER_ROLE_OWNER           => 'owner.kb.article',
                USER_ROLE_AFFILIATE       => 'affiliate.kb.article',
                USER_ROLE_FINANCE_PARTNER => 'finance-partner.kb.article',
            ];
            $name = $routeByRole[$role] ?? null;
            if ($name && \Illuminate\Support\Facades\Route::has($name)) {
                return route($name, $article->slug);
            }
        }

        return $this->link_url;
    }

    public function scopeLive(Builder $q): Builder
    {
        return $q->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /** 'all' audience, or the given role id is in the comma-separated list. */
    public function scopeForRole(Builder $q, int $role): Builder
    {
        return $q->where(function ($w) use ($role) {
            $w->where('audience', 'all')
              ->orWhere('audience', (string) $role)
              ->orWhere('audience', 'like', $role . ',%')
              ->orWhere('audience', 'like', '%,' . $role)
              ->orWhere('audience', 'like', '%,' . $role . ',%');
        });
    }

    /** The unseen, live announcements for a user (role-targeted), newest first. */
    public static function unseenFor(User $user)
    {
        return static::live()
            ->with('kbArticle')
            ->forRole((int) $user->role)
            ->whereDoesntHave('seenBy', fn ($q) => $q->where('users.id', $user->id))
            ->latest('published_at')
            ->get();
    }
}
