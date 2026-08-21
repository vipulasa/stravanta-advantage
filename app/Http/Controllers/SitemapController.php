<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Support\Seo\SeoData;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Serve the XML sitemap.
     *
     * Hand-rolled rather than pulled from a package: the site has four kinds
     * of URL and no build step to hang a generated file off, so a live query
     * is both smaller and always current.
     *
     * Only canonical URLs appear here. Paged listings are excluded because
     * they are served `noindex`, and submitting a URL that tells the crawler
     * not to index it is a contradiction Search Console reports as an error.
     */
    public function __invoke(): Response
    {
        $posts = Post::query()
            ->published()
            ->latest('published_at')
            ->get(['slug', 'published_at', 'updated_at']);

        $urls = [
            [
                'loc' => SeoData::homeUrl(),
                'lastmod' => $posts->max('updated_at'),
                'changefreq' => 'monthly',
                'priority' => '1.0',
            ],
            [
                'loc' => route('contact'),
                'lastmod' => null,
                'changefreq' => 'yearly',
                'priority' => '0.8',
            ],
            [
                'loc' => route('blog.index'),
                'lastmod' => $posts->max('published_at'),
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
        ];

        foreach (Category::query()->orderBy('name')->get() as $category) {
            $urls[] = [
                'loc' => route('blog.index', ['category' => $category->slug]),
                'lastmod' => null,
                'changefreq' => 'weekly',
                'priority' => '0.5',
            ];
        }

        foreach ($posts as $post) {
            $urls[] = [
                'loc' => route('blog.show', $post->slug),
                'lastmod' => $post->updated_at ?? $post->published_at,
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        }

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
