<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Support\Seo\SchemaGraph;
use App\Support\Seo\SeoData;
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

        $categories = Category::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category): array => [
                'name' => $category->name,
                'slug' => $category->slug,
            ]);

        return Inertia::render('blog/index', [
            'posts' => $posts,
            'categories' => $categories,
            // An unknown slug simply yields an empty listing; echoing it back
            // keeps the filter chips honest about what was asked for.
            'activeCategory' => $categorySlug === '' ? null : $categorySlug,
            'seo' => $this->seo(
                $request,
                $posts->currentPage(),
                array_values($posts->items()),
                array_values($categories->all()),
            )->toArray(),
        ]);
    }

    /**
     * Build the SEO data for a listing page.
     *
     * A filtered or paged listing is a genuinely different set of articles, so
     * each gets its own title and its own self-referential canonical rather
     * than pointing every variation at the bare `/blog`. Everything past the
     * first page is kept out of the index — those pages hold no content of
     * their own and only compete with the articles they link to.
     *
     * @param  list<array{title: string, slug: string}>  $posts
     * @param  list<array{name: string, slug: string}>  $categories
     */
    private function seo(Request $request, int $page, array $posts, array $categories): SeoData
    {
        $categorySlug = $request->string('category')->toString();
        $categoryName = collect($categories)->firstWhere('slug', $categorySlug)['name'] ?? null;

        $title = $categoryName === null
            ? 'Insights — STRAVANTA Advisory'
            : sprintf('%s insights — STRAVANTA Advisory', $categoryName);

        if ($page > 1) {
            $title = sprintf('%s (page %d)', $title, $page);
        }

        $description = $categoryName === null
            ? 'Operator-led thinking on operational excellence, practical AI, governance and predictable delivery.'
            : sprintf('Operator-led thinking on %s, from the STRAVANTA Advisory team.', mb_strtolower($categoryName));

        // The two parameters that make one listing different from another;
        // anything else a shared link carries is not part of its identity.
        $url = SeoData::canonicalFor($request, ['category', 'page']);

        // A slug that matches no category yields an empty listing. It is a
        // real 200, so it has to say for itself that it is not worth indexing.
        $unknownCategory = $categorySlug !== '' && $categoryName === null;

        $seo = SeoData::make($title, $description, $url)
            ->withSchema(
                SchemaGraph::make()
                    ->siteIdentity()
                    ->webPage($title, $description, $url, 'CollectionPage')
                    ->postList($posts, $url)
                    ->breadcrumbs([
                        ['name' => 'Home', 'url' => SeoData::homeUrl()],
                        ['name' => 'Insights', 'url' => route('blog.index')],
                    ]),
            );

        return $page > 1 || $unknownCategory ? $seo->withRobots('noindex, follow') : $seo;
    }
}
