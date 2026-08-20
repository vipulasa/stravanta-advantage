<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogIndexController extends Controller
{
    /**
     * How many articles a listing page shows.
     */
    private const PER_PAGE = 9;

    /**
     * Show the published articles, optionally narrowed to one category.
     */
    public function __invoke(Request $request): Response
    {
        $categorySlug = $request->string('category')->toString();

        $posts = Post::query()
            ->published()
            ->with(['category', 'author', 'media'])
            ->when(
                $categorySlug !== '',
                fn ($query) => $query->whereHas(
                    'category',
                    fn ($category) => $category->where('slug', $categorySlug),
                ),
            )
            ->latest('published_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString()
            ->through(fn (Post $post): array => $post->toCardArray());

        return Inertia::render('blog/index', [
            'posts' => $posts,
            'categories' => Category::query()
                ->orderBy('name')
                ->get()
                ->map(fn (Category $category): array => [
                    'name' => $category->name,
                    'slug' => $category->slug,
                ]),
            // An unknown slug simply yields an empty listing; echoing it back
            // keeps the filter chips honest about what was asked for.
            'activeCategory' => $categorySlug === '' ? null : $categorySlug,
        ]);
    }
}
