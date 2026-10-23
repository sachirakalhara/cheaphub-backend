<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product\Bulk\BulkProduct;
use App\Models\Product\Contribution\ContributionProduct;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    /**
     * Sitemap of public product pages on the storefront, referenced from
     * cheaphub.io/robots.txt. Only listed ('open') products are included —
     * unlisted (reseller-link) and on-hold products must never appear here.
     */
    public function products()
    {
        $xml = Cache::remember('sitemap:products', 3600, function () {
            $base = rtrim(config('app.storefront_url', 'https://cheaphub.io'), '/');
            $urls = [];

            BulkProduct::where('visibility', 'open')->get(['id', 'name', 'updated_at'])
                ->each(function ($p) use (&$urls, $base) {
                    $urls[] = [$base . '/bulk/' . $p->id . '/' . (Str::slug($p->name) ?: 'product'), $p->updated_at];
                });

            ContributionProduct::where('visibility', 'open')->get(['id', 'name', 'updated_at'])
                ->each(function ($p) use (&$urls, $base) {
                    $urls[] = [$base . '/contribution/' . $p->id . '/' . (Str::slug($p->name) ?: 'product'), $p->updated_at];
                });

            $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $out .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
            foreach ($urls as [$loc, $updated]) {
                $out .= "  <url>\n    <loc>" . htmlspecialchars($loc, ENT_XML1) . "</loc>\n";
                if ($updated) {
                    $out .= '    <lastmod>' . $updated->toDateString() . "</lastmod>\n";
                }
                $out .= "  </url>\n";
            }
            return $out . "</urlset>\n";
        });

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
