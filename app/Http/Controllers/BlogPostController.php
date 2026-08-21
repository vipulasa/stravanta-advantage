<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\Seo\SchemaGraph;
use App\Support\Seo\SeoData;
use Inertia\Inertia;
use Inertia\Response;

class BlogPostController extends Controller
{
    /**
     * How many further articles are suggested at the foot of an article.
     */
    private const RELATED_LIMIT = 3;

    /**
     * Show a single published article.
     *
     * The route binds on the slug alone, so an unpublished post would resolve
     * here; it is turned away rather than rendered.
     */
    public function __invoke(Post $post): Response
    {
        abort_unless($post->isPublished(), 404);

        $post->load(['category', 'author', 'media']);

        $body = $post->renderRichContent('body');

        return Inertia::render('blog/show', [
            'post' => [
                ...$post->toCardArray(),
                'image' => $post->featuredImageUrl(Post::HERO_CONVERSION),
                // Rich content is registered on the model, so the raw column is
                // not renderable HTML — embedded attachments need resolving.
                'body' => $body,
            ],
            'related' => Post::query()
                ->published()
                ->with(['category', 'author', 'media'])
                ->whereKeyNot($post->getKey())
                ->when(
                    $post->category_id !== null,
                    fn ($query) => $query->where('category_id', $post->category_id),
                )
                ->latest('published_at')
                ->limit(self::RELATED_LIMIT)
                ->get()
                ->map(fn (Post $related): array => $related->toCardArray()),
            'seo' => $this->seo($post, $body)->toArray(),
        ]);
    }

    /**
     * Build the SEO data for an article.
     *
     * The admin's `meta_title` and `meta_description` win where they are set;
     * the article's own title and excerpt stand in where they are not, so a
     * post is never published with an empty description.
     */
    private function seo(Post $post, string $body): SeoData
    {
        $url = route('blog.show', $post);
        $image = $post->featuredImageUrl(Post::HERO_CONVERSION);

        $title = sprintf('%s — STRAVANTA Advisory', $post->meta_title ?? $post->title);
        $description = $post->meta_description
            ?? $post->excerpt
            ?? config('seo.default_description');

        $crumbs = [
            ['name' => 'Home', 'url' => SeoData::homeUrl()],
            ['name' => 'Insights', 'url' => route('blog.index')],
        ];

        if ($post->category !== null) {
            $crumbs[] = [
                'name' => $post->category->name,
                'url' => route('blog.index', ['category' => $post->category->slug]),
            ];
        }

        $crumbs[] = ['name' => $post->title, 'url' => $url];

        return SeoData::make($title, $description, $url)
            ->withType('article')
            ->withImage($image, $post->title)
            ->withArticle(
                $post->published_at?->toIso8601String(),
                $post->updated_at?->toIso8601String(),
                $post->author?->name,
                $post->category?->name,
            )
            ->withSchema(
                SchemaGraph::make()
                    ->siteIdentity()
                    ->webPage($title, $description, $url)
                    ->blogPosting([
                        // The headline is the article's own title, not the
                        // SEO title: the suffix is for a browser tab and a
                        // search result, and does not belong in the graph.
                        'title' => $post->title,
                        'description' => $description,
                        'url' => $url,
                        'image' => $image === null ? null : SeoData::absolute($image),
                        'published_at' => $post->published_at?->toIso8601String(),
                        'modified_at' => $post->updated_at?->toIso8601String(),
                        'author' => $post->author?->name,
                        'section' => $post->category?->name,
                        'word_count' => str_word_count(strip_tags($body)) ?: null,
                    ])
                    ->breadcrumbs($crumbs),
            );
    }
}
