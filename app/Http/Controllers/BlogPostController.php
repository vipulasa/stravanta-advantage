<?php

namespace App\Http\Controllers;

use App\Models\Post;
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

        return Inertia::render('blog/show', [
            'post' => [
                ...$post->toCardArray(),
                'image' => $post->featuredImageUrl(Post::HERO_CONVERSION),
                // Rich content is registered on the model, so the raw column is
                // not renderable HTML — embedded attachments need resolving.
                'body' => $post->renderRichContent('body'),
                'meta_title' => $post->meta_title,
                'meta_description' => $post->meta_description,
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
        ]);
    }
}
