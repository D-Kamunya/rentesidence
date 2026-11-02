<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Support\Facades\Route;

/**
 * Public XML sitemap for search engines (submitted in Google Search Console).
 * Lists the stable public surfaces + every PUBLISHED blog post, so new content is
 * discovered and crawled promptly. Guarded with Route::has() because some public
 * marketing routes only register when the SaaS front end is enabled.
 */
class SitemapController extends Controller
{
    public function index()
    {
        $urls = [];

        // Homepage — highest priority.
        $urls[] = ['loc' => url('/'), 'changefreq' => 'weekly', 'priority' => '1.0'];

        // Blog index — updated often as we publish.
        if (Route::has('blog.index')) {
            $urls[] = ['loc' => route('blog.index'), 'changefreq' => 'daily', 'priority' => '0.8'];
        }

        // Stable public marketing / policy pages (only if the front end is enabled).
        $named = [
            'affiliate.apply'   => '0.7',
            'get-started'       => '0.6',
            'terms-conditions'  => '0.3',
            'privacy-policy'    => '0.3',
            'cookie-policy'     => '0.3',
        ];
        foreach ($named as $name => $priority) {
            if (Route::has($name)) {
                $urls[] = ['loc' => route($name), 'changefreq' => 'monthly', 'priority' => $priority];
            }
        }

        // Blog categories (archive pages).
        if (Route::has('blog.index')) {
            BlogCategory::query()->get(['slug'])->each(function ($cat) use (&$urls) {
                if ($cat->slug) {
                    $urls[] = ['loc' => route('blog.index', ['category' => $cat->slug]), 'changefreq' => 'weekly', 'priority' => '0.5'];
                }
            });
        }

        // Every published post — the content we most want indexed.
        if (Route::has('blog.show')) {
            BlogPost::published()->orderByDesc('published_at')->get()->each(function ($post) use (&$urls) {
                $urls[] = [
                    'loc'        => route('blog.show', $post->slug),
                    'lastmod'    => optional($post->updated_at ?? $post->published_at)->toAtomString(),
                    'changefreq' => 'monthly',
                    'priority'   => '0.7',
                ];
            });
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1) . '</loc>' . "\n";
            if (! empty($u['lastmod'])) {
                $xml .= '    <lastmod>' . $u['lastmod'] . '</lastmod>' . "\n";
            }
            $xml .= '    <changefreq>' . $u['changefreq'] . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $u['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
