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

    public function index()
    {
        return view('admin.feature-announcements.index', [
            'announcements' => FeatureAnnouncement::with('kbArticle')->latest()->paginate(20),
            'audiences'     => self::AUDIENCES,
            'kbArticles'    => \App\Models\KnowledgeBaseArticle::orderBy('title')->get(['id', 'title', 'audience']),
            'pageTitle'     => __('Feature Announcements'),
        ]);
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
