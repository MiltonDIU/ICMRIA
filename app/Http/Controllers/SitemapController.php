<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Speaker;

/**
 * Search-engine files: robots.txt and sitemap.xml.
 * Only public pages are listed; the portal (admin, payment, auth) and the
 * internal user guides are kept out (they also carry a noindex tag).
 */
class SitemapController extends Controller
{
    public function robots()
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /onecard',
            'Disallow: /success',
            'Disallow: /cancel',
            'Disallow: /fail',
            'Disallow: /unsubscribe',
            'Disallow: /subscribe',
            'Disallow: /generate_ids',
            'Disallow: /clear-cache',
            'Disallow: /password',
            'Disallow: /email',
            // /documents/user-guide is deliberately not disallowed: Google must
            // fetch those pages to see their noindex tag.
            '',
            'Sitemap: ' . url('sitemap.xml'),
        ];

        return response(implode("\n", $lines) . "\n", 200)->header('Content-Type', 'text/plain');
    }

    public function sitemap()
    {
        $urls = [];
        $add = function (string $loc, $lastmod = null, string $priority = '0.6') use (&$urls) {
            $urls[] = ['loc' => $loc, 'lastmod' => $lastmod ? $lastmod->toAtomString() : null, 'priority' => $priority];
        };

        $add(route('home'), null, '1.0');
        foreach (['callForPepper', 'author-guidelines', 'tracks', 'camera-ready-guidelines',
                  'accommodation-transportation', 'book-ticket', 'blogs', 'privacy-policy'] as $name) {
            $add(route($name), null, $name === 'privacy-policy' ? '0.3' : '0.8');
        }

        Speaker::whereNotNull('slug')->where('slug', '!=', '')->get(['slug', 'updated_at'])
            ->each(fn ($s) => $add(route('speaker', ['slug' => $s->slug]), $s->updated_at));

        Post::where('is_active', '1')->get(['id', 'slug', 'updated_at'])
            ->each(fn ($p) => $add(route('blogDetails', [$p->id, $p->slug]), $p->updated_at, '0.5'));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
             . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= '  <url><loc>' . e($u['loc']) . '</loc>'
                  . ($u['lastmod'] ? '<lastmod>' . $u['lastmod'] . '</lastmod>' : '')
                  . '<priority>' . $u['priority'] . '</priority></url>' . "\n";
        }
        $xml .= '</urlset>' . "\n";

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
