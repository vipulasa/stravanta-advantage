<?php

use App\Models\Category;
use App\Models\Post;
use App\Support\Seo\SeoData;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;

/**
 * These tests read the rendered HTML rather than the Inertia props on purpose.
 *
 * `phpunit.xml` disables SSR, so every response here goes through the Blade
 * fallback in `resources/views/components/seo.blade.php` — which is exactly the
 * markup a social scraper or a crawler sees when the SSR process is not up.
 * Asserting on props alone would leave that renderer entirely untested.
 */

/**
 * Pull the `content` of a meta tag out of a response.
 */
function metaContent(TestResponse $response, string $attribute, string $name): ?string
{
    $pattern = sprintf(
        '/<meta[^>]*%s="%s"[^>]*content="([^"]*)"/',
        $attribute,
        preg_quote($name, '/'),
    );

    preg_match($pattern, $response->getContent(), $matches);

    return $matches[1] ?? null;
}

/**
 * Decode the page's JSON-LD graph.
 *
 * @return list<array<string, mixed>>
 */
function schemaGraph(TestResponse $response): array
{
    preg_match(
        '/<script[^>]*type="application\/ld\+json"[^>]*>(.*?)<\/script>/s',
        $response->getContent(),
        $matches,
    );

    expect($matches)->not->toBeEmpty('the page rendered no JSON-LD');

    $decoded = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);

    expect($decoded['@context'])->toBe('https://schema.org');

    return $decoded['@graph'];
}

/**
 * Find the first node of a given `@type` in a graph.
 *
 * @param  list<array<string, mixed>>  $graph
 * @return array<string, mixed>|null
 */
function schemaNode(array $graph, string $type): ?array
{
    return collect($graph)->firstWhere('@type', $type);
}

/**
 * Create a published post with the relations the article page eager loads.
 *
 * @param  array<string, mixed>  $attributes
 */
function seoPost(array $attributes = []): Post
{
    return Post::factory()->published()->create($attributes);
}

/**
 * Run the rest of the test as though the app were deployed.
 *
 * The crawler directives deliberately depend on the environment — a staging
 * copy forces `noindex` — so the production behaviour cannot be reached any
 * other way from the test suite.
 */
function asProduction(): void
{
    app()->detectEnvironment(fn (): string => 'production');
}

/*
|--------------------------------------------------------- every public page ---
*/

test('every public page renders a complete tag set', function (string $url) {
    $response = $this->get($url)->assertOk();

    expect($response->getContent())->toMatch('/<title data-inertia="">.+<\/title>/');

    expect(metaContent($response, 'name', 'description'))->not->toBeEmpty();
    expect(metaContent($response, 'property', 'og:title'))->not->toBeEmpty();
    expect(metaContent($response, 'property', 'og:description'))->not->toBeEmpty();
    expect(metaContent($response, 'property', 'og:site_name'))->toBe('STRAVANTA Advisory');
    expect(metaContent($response, 'property', 'og:locale'))->toBe('en_GB');
    expect(metaContent($response, 'property', 'og:image'))->toStartWith(config('app.url'));
    expect(metaContent($response, 'name', 'twitter:card'))->toBe('summary_large_image');
    expect(metaContent($response, 'name', 'twitter:title'))->not->toBeEmpty();
    expect(metaContent($response, 'name', 'twitter:image'))->not->toBeEmpty();

    $response->assertSee('rel="canonical"', false);
})->with(fn () => [
    '/',
    '/contact',
    '/blog',
]);

test('og:url always matches the canonical', function (string $url) {
    $response = $this->get($url)->assertOk();

    preg_match('/<link[^>]*rel="canonical"[^>]*href="([^"]*)"/', $response->getContent(), $matches);

    expect($matches[1])->toBe(metaContent($response, 'property', 'og:url'));
})->with(['/', '/contact', '/blog']);

test('every public page carries the organization and website nodes', function (string $url) {
    $graph = schemaGraph($this->get($url)->assertOk());

    $organization = schemaNode($graph, 'Organization');
    $website = schemaNode($graph, 'WebSite');

    expect($organization['@id'])->toBe(config('app.url').'/#organization')
        ->and($organization['name'])->toBe('STRAVANTA Advisory')
        ->and($organization['logo']['url'])->toStartWith(config('app.url'))
        // Never emit an empty sameAs: an Organization with no profiles is
        // correct, one claiming zero profiles reads as a broken integration.
        ->and($organization)->not->toHaveKey('sameAs')
        ->and($website['publisher']['@id'])->toBe($organization['@id']);
})->with(['/', '/contact', '/blog']);

/*
|-------------------------------------------------------------------- home ---
*/

test('the home page carries the approved title and the service catalogue', function () {
    $response = $this->get(route('home'))->assertOk();

    $response->assertSee(
        '<title data-inertia="">STRAVANTA Advisory | Turn Ambition Into Advantage</title>',
        false,
    );

    expect(metaContent($response, 'property', 'og:type'))->toBe('website');

    $catalog = schemaNode(schemaGraph($response), 'OfferCatalog');

    expect($catalog['itemListElement'])->toHaveCount(3)
        ->and($catalog['itemListElement'][0]['itemOffered']['name'])->toBe('Business Advantage Scan')
        ->and($catalog['itemListElement'][0]['position'])->toBe(1);
});

/*
|----------------------------------------------------------------- contact ---
*/

test('the contact page is marked up as a ContactPage with breadcrumbs', function () {
    $graph = schemaGraph($this->get(route('contact'))->assertOk());

    expect(schemaNode($graph, 'ContactPage')['url'])->toBe(route('contact'));

    $crumbs = schemaNode($graph, 'BreadcrumbList')['itemListElement'];

    expect($crumbs)->toHaveCount(2)
        ->and($crumbs[0]['item'])->toBe(SeoData::homeUrl())
        ->and($crumbs[1]['name'])->toBe('Contact');
});

/*
|------------------------------------------------------------- blog index ---
*/

test('the blog index lists its posts as an ItemList', function () {
    seoPost(['title' => 'Where the constraint hides']);

    $graph = schemaGraph($this->get(route('blog.index'))->assertOk());

    expect(schemaNode($graph, 'CollectionPage'))->not->toBeNull();

    $list = schemaNode($graph, 'ItemList');

    expect($list['numberOfItems'])->toBe(1)
        ->and($list['itemListElement'][0]['name'])->toBe('Where the constraint hides');
});

test('a filtered listing gets its own title and canonical', function () {
    $category = Category::query()->firstOrFail();

    $response = $this->get(route('blog.index', ['category' => $category->slug]))->assertOk();

    $response->assertSee(sprintf('%s insights — STRAVANTA Advisory', $category->name), false);

    preg_match('/<link[^>]*rel="canonical"[^>]*href="([^"]*)"/', $response->getContent(), $matches);

    expect($matches[1])->toBe(route('blog.index', ['category' => $category->slug]));
});

test('tracking parameters are stripped from the canonical', function () {
    $category = Category::query()->firstOrFail();

    $response = $this->get(route('blog.index', [
        'category' => $category->slug,
        'utm_source' => 'newsletter',
        'fbclid' => 'IwAR0',
    ]))->assertOk();

    preg_match('/<link[^>]*rel="canonical"[^>]*href="([^"]*)"/', $response->getContent(), $matches);

    expect($matches[1])->toBe(route('blog.index', ['category' => $category->slug]));
});

test('a listing filtered on an unknown category is kept out of the index', function () {
    asProduction();

    $response = $this->get(route('blog.index', ['category' => 'no-such-category']))->assertOk();

    expect(metaContent($response, 'name', 'robots'))->toBe('noindex, follow');
});

test('listing pages past the first are kept out of the index', function () {
    asProduction();

    Post::factory()->published()->count(11)->create();

    expect(metaContent($this->get(route('blog.index')), 'name', 'robots'))
        ->toStartWith('index, follow');

    $response = $this->get(route('blog.index', ['page' => 2]))->assertOk();

    expect(metaContent($response, 'name', 'robots'))->toBe('noindex, follow');
    $response->assertSee('(page 2)', false);
});

/*
|-------------------------------------------------------------- blog post ---
*/

test('an article renders article tags and a BlogPosting node', function () {
    $category = Category::query()->firstOrFail();

    $post = seoPost([
        'title' => 'Predictable delivery is a system, not a promise',
        'excerpt' => 'Why delivery dates slip, and what actually fixes it.',
        'category_id' => $category->id,
        'published_at' => Carbon::parse('2026-03-04 09:00:00'),
    ]);

    $response = $this->get(route('blog.show', $post))->assertOk();

    $response->assertSee(
        sprintf('<title data-inertia="">%s — STRAVANTA Advisory</title>', $post->title),
        false,
    );

    expect(metaContent($response, 'property', 'og:type'))->toBe('article')
        ->and(metaContent($response, 'property', 'article:published_time'))
        ->toBe($post->published_at->toIso8601String())
        ->and(metaContent($response, 'property', 'article:section'))->toBe($category->name)
        ->and(metaContent($response, 'name', 'description'))->toBe($post->excerpt);

    $article = schemaNode(schemaGraph($response), 'BlogPosting');

    expect($article['headline'])->toBe($post->title)
        ->and($article['url'])->toBe(route('blog.show', $post))
        ->and($article['datePublished'])->toBe($post->published_at->toIso8601String())
        ->and($article['articleSection'])->toBe($category->name)
        ->and($article['publisher']['@id'])->toBe(config('app.url').'/#organization');
});

test('an article prefers the meta fields the admin wrote', function () {
    $post = seoPost([
        'title' => 'The title on the page',
        'excerpt' => 'The excerpt on the page.',
        'meta_title' => 'The title in search results',
        'meta_description' => 'The description in search results.',
    ]);

    $response = $this->get(route('blog.show', $post))->assertOk();

    $response->assertSee(
        '<title data-inertia="">The title in search results — STRAVANTA Advisory</title>',
        false,
    );

    expect(metaContent($response, 'name', 'description'))->toBe('The description in search results.');

    // The graph's headline stays the article's real title — the suffix belongs
    // to the browser tab, not to the structured data.
    expect(schemaNode(schemaGraph($response), 'BlogPosting')['headline'])->toBe('The title on the page');
});

test('an article breadcrumb trail includes its category', function () {
    $category = Category::query()->firstOrFail();
    $post = seoPost(['category_id' => $category->id]);

    $crumbs = schemaNode(schemaGraph($this->get(route('blog.show', $post))), 'BreadcrumbList')['itemListElement'];

    expect($crumbs)->toHaveCount(4)
        ->and($crumbs[2]['name'])->toBe($category->name)
        ->and($crumbs[2]['item'])->toBe(route('blog.index', ['category' => $category->slug]))
        ->and($crumbs[3]['item'])->toBe(route('blog.show', $post));
});

test('an article without an excerpt still gets a description', function () {
    $post = seoPost(['excerpt' => null, 'meta_description' => null]);

    expect(metaContent($this->get(route('blog.show', $post)), 'name', 'description'))
        ->toBe(config('seo.default_description'));
});

/*
|------------------------------------------------------------ ld+json safety ---
*/

test('a post title cannot break out of the JSON-LD script element', function () {
    $post = seoPost(['title' => 'Breaking out</script><script>alert(1)</script>']);

    $response = $this->get(route('blog.show', $post))->assertOk();

    $response->assertDontSee('</script><script>alert(1)', false);

    // The title survives intact once the JSON is parsed — it is escaped, not
    // stripped.
    expect(schemaNode(schemaGraph($response), 'BlogPosting')['headline'])->toBe($post->title);
});

/*
|-------------------------------------------------------- crawler directives ---
*/

test('non-production environments are kept out of the index', function () {
    expect(metaContent($this->get(route('home')), 'name', 'robots'))->toBe('noindex, nofollow');

    $this->get('/robots.txt')->assertOk()->assertSee("User-agent: *\nDisallow: /");
});

test('production serves the configured crawler directives', function () {
    asProduction();

    expect(metaContent($this->get(route('home')), 'name', 'robots'))->toBe(config('seo.robots'));
});

test('robots.txt points at the sitemap and shields the admin panel', function () {
    asProduction();

    $this->get('/robots.txt')->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=utf-8')
        ->assertSee('Disallow: /_admin')
        ->assertSee('Allow: /')
        ->assertSee('Sitemap: '.route('sitemap'));
});

/*
|----------------------------------------------------------------- sitemap ---
*/

test('the sitemap lists every canonical URL', function () {
    $post = seoPost();

    $response = $this->get('/sitemap.xml')->assertOk();

    $response->assertHeader('Content-Type', 'application/xml; charset=utf-8')
        ->assertSee(SeoData::homeUrl())
        ->assertSee(route('contact'))
        ->assertSee(route('blog.index'))
        ->assertSee(route('blog.show', $post));

    $xml = new SimpleXMLElement($response->getContent());

    expect($xml->getName())->toBe('urlset')
        ->and(count($xml->url))->toBeGreaterThan(0);
});

test('the sitemap omits unpublished posts', function () {
    $draft = Post::factory()->create(['published_at' => null]);

    $this->get('/sitemap.xml')->assertOk()->assertDontSee($draft->slug);
});
