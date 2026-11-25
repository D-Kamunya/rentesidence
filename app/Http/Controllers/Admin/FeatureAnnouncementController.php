<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureAnnouncement;
use Illuminate\Http\Request;

/** Admin CRUD for "what's new" feature announcements shown in-account to the chosen audience. */
class FeatureAnnouncementController extends Controller
{
    /** Roles an announcement can target. */
    public const AUDIENCES = [
        'all'                     => 'Everyone',
        USER_ROLE_OWNER           => 'Owners',
        USER_ROLE_TENANT          => 'Tenants',
        USER_ROLE_AFFILIATE       => 'Affiliates',
        USER_ROLE_FINANCE_PARTNER => 'Finance partners',
        USER_ROLE_MAINTAINER      => 'Maintainers',
    ];

    /** Roles that actually see in-account announcements (admins don't) — the denominator for reach. */
    public const VISIBLE_ROLES = [
        USER_ROLE_OWNER, USER_ROLE_TENANT, USER_ROLE_AFFILIATE, USER_ROLE_FINANCE_PARTNER, USER_ROLE_MAINTAINER,
    ];

    public function index()
    {
        $announcements = FeatureAnnouncement::with('kbArticle')
            // "Seen" = a pivot row with a seen_at stamp (written when the user dismisses the modal).
            ->withCount(['seenBy as reach_seen' => fn ($q) => $q->whereNotNull('feature_announcement_user.seen_at')])
            ->latest()
            ->paginate(20);

        // Audience size per role, counted once, then summed per announcement — so an admin can see
        // what share of the targeted people have actually seen each note and know when to pause it.
        $roleCounts = \App\Models\User::whereIn('role', self::VISIBLE_ROLES)
            ->selectRaw('role, COUNT(*) as c')->groupBy('role')->pluck('c', 'role');

        foreach ($announcements as $a) {
            $reach = self::computeReach((string) $a->audience, (int) $a->reach_seen, $roleCounts);
            $a->reach_target = $reach['target'];
            $a->reach_pct    = $reach['pct'];
        }

        return view('admin.feature-announcements.index', [
            'announcements' => $announcements,
            'audiences'     => self::AUDIENCES,
            'kbArticles'    => \App\Models\KnowledgeBaseArticle::orderBy('title')->get(['id', 'title', 'audience']),
            'pageTitle'     => __('Feature Announcements'),
        ]);
    }

    /**
     * Reach for one announcement: how many targeted people have seen it, out of the audience size.
     * Pure so it's unit-testable. `$seen` is the seen-count; `$roleCounts` maps role id → user count.
     *
     * @return array{target:int,seen:int,pct:?int}  pct is null when the audience is empty.
     */
    public static function computeReach(string $audience, int $seen, \Illuminate\Support\Collection $roleCounts): array
    {
        $roles = $audience === 'all'
            ? self::VISIBLE_ROLES
            : array_map('intval', array_filter(explode(',', $audience), fn ($r) => $r !== ''));

        $target = 0;
        foreach ($roles as $r) {
            $target += (int) ($roleCounts[$r] ?? 0);
        }

        // Cap seen at target — a stale pivot row (e.g. a user whose role changed) must never read >100%.
        $pct = $target > 0 ? (int) round(min($seen, $target) / $target * 100) : null;

        return ['target' => $target, 'seen' => $seen, 'pct' => $pct];
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['published_at'] = ($request->boolean('publish')) ? now() : null;
        FeatureAnnouncement::create($data);

        return redirect()->route('admin.feature-announcements.index')
            ->with('success', __('Announcement created.'));
    }

    public function update(Request $request, FeatureAnnouncement $announcement)
    {
        $data = $this->validated($request);
        // Once published, stay published across edits; a draft publishes when "publish" is ticked.
        $data['published_at'] = $announcement->published_at ?? ($request->boolean('publish') ? now() : null);
        $announcement->update($data);

        return redirect()->route('admin.feature-announcements.index')
            ->with('success', __('Announcement updated.'));
    }

    /** Quick activate / deactivate without opening the editor. */
    public function toggle(FeatureAnnouncement $announcement)
    {
        $announcement->update(['is_active' => ! $announcement->is_active]);

        return back()->with('success', $announcement->is_active ? __('Announcement activated.') : __('Announcement paused.'));
    }

    public function destroy(FeatureAnnouncement $announcement)
    {
        $announcement->delete();

        return back()->with('success', __('Announcement deleted.'));
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title'         => 'required|string|max:120',
            'body'          => 'required|string|max:2000',
            'icon'          => 'nullable|string|max:8',
            'kb_article_id' => 'nullable|integer|exists:' . (new \App\Models\KnowledgeBaseArticle)->getTable() . ',id',
            'link_url'      => 'nullable|url|max:500',
            'link_label'    => 'nullable|string|max:40',
            'audience'      => 'required|array|min:1',
            'audience.*'    => 'string',
            'is_active'     => 'nullable|boolean',
            'publish'       => 'nullable|boolean',
        ]);

        // "all" wins if selected; otherwise a comma-separated list of role ids.
        $audience = in_array('all', $data['audience'], true) ? 'all' : implode(',', $data['audience']);

        // A picked KB article takes precedence over a pasted URL (resolvedLink prefers it anyway;
        // clear link_url so the two don't drift).
        $kbId = $data['kb_article_id'] ?? null;

        return [
            'title'         => $data['title'],
            'body'          => $data['body'],
            'icon'          => $data['icon'] ?? null,
            'kb_article_id' => $kbId,
            'link_url'      => $kbId ? null : ($data['link_url'] ?? null),
            'link_label'    => $data['link_label'] ?? null,
            'audience'      => $audience,
            'is_active'     => (bool) ($data['is_active'] ?? false),
        ];
    }
}
