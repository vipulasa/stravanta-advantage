<?php

namespace App\Support\Seo;

/**
 * Builds the schema.org JSON-LD graph for a page.
 *
 * One `@graph` per page rather than several separate `<script>` blocks: nodes
 * carry stable `@id`s, so the Organization and WebSite are declared once and
 * everything else — the page, the article, the breadcrumbs — points back at
 * them by reference instead of restating them.
 *
 * Nothing here invents facts. Optional properties are dropped when the value
 * behind them is missing, because fabricated structured data is penalised more
 * heavily than absent structured data.
 */
class SchemaGraph
{
    /**
     * The nodes collected so far, in the order they were added.
     *
     * @var list<array<string, mixed>>
     */
    private array $nodes = [];

    public static function make(): self
    {
        return new self;
    }

    /**
     * The `@id` of the Organization node.
     *
     * A fragment on the site root rather than a bare URL, so the identifier is
     * globally unique but never resolves to a page of its own.
     */
    public static function organizationId(): string
    {
        return SeoData::absolute('/').'#organization';
    }

    /**
     * The `@id` of the WebSite node.
     */
    public static function websiteId(): string
    {
        return SeoData::absolute('/').'#website';
    }

    /**
     * Add the Organization and WebSite nodes.
     *
     * Every page gets these: they are what search engines use to build the
     * knowledge panel, and they have to be present on whichever page happens
     * to get crawled first.
     */
    public function siteIdentity(): self
    {
        $organization = config('seo.organization');
        $logo = $organization['logo'];

        $node = [
            '@type' => 'Organization',
            '@id' => self::organizationId(),
            'name' => $organization['name'],
            'legalName' => $organization['legal_name'],
            'url' => SeoData::absolute('/'),
            'description' => $organization['description'],
            'slogan' => $organization['slogan'],
            'logo' => [
                '@type' => 'ImageObject',
                '@id' => SeoData::absolute('/').'#logo',
                'url' => SeoData::absolute($logo['path']),
                'contentUrl' => SeoData::absolute($logo['path']),
                'width' => $logo['width'],
                'height' => $logo['height'],
                'caption' => $organization['name'],
            ],
            'image' => ['@id' => SeoData::absolute('/').'#logo'],
            'areaServed' => array_map(
                fn (string $area): array => ['@type' => 'Place', 'name' => $area],
                $organization['area_served'],
            ),
            'knowsAbout' => $organization['knows_about'],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'sales',
                'email' => $organization['email'],
                'availableLanguage' => ['English'],
            ],
        ];

        if ($organization['same_as'] !== []) {
            $node['sameAs'] = $organization['same_as'];
        }

        $this->nodes[] = $node;

        $this->nodes[] = [
            '@type' => 'WebSite',
            '@id' => self::websiteId(),
            'url' => SeoData::absolute('/'),
            'name' => config('seo.site_name'),
            'description' => config('seo.default_description'),
            'publisher' => ['@id' => self::organizationId()],
            'inLanguage' => config('seo.locale'),
        ];

        return $this;
    }

    /**
     * Add the Organization's service catalogue.
     *
     * Only meaningful on the home page, where the services are actually listed.
     */
    public function serviceCatalog(): self
    {
        $services = config('seo.services');
        $organization = config('seo.organization');

        $this->nodes[] = [
            '@type' => 'OfferCatalog',
            '@id' => SeoData::absolute('/').'#services',
            'name' => 'Advisory services',
            'provider' => ['@id' => self::organizationId()],
            'itemListElement' => $this->positioned(
                $services,
                fn (array $service, int $position): array => [
                    '@type' => 'Offer',
                    'position' => $position,
                    'itemOffered' => [
                        '@type' => 'Service',
                        'name' => $service['name'],
                        'description' => $service['description'],
                        'serviceType' => $service['name'],
                        'provider' => ['@id' => self::organizationId()],
                        'areaServed' => $organization['area_served'],
                    ],
                ],
            ),
        ];

        return $this;
    }

    /**
     * Add the page itself.
     *
     * `$type` narrows the node for pages that have a more specific meaning —
     * `ContactPage` for the enquiry form, `CollectionPage` for the listing.
     */
    public function webPage(string $title, string $description, string $url, string $type = 'WebPage'): self
    {
        $this->nodes[] = [
            '@type' => $type,
            '@id' => $url.'#webpage',
            'url' => $url,
            'name' => $title,
            'description' => $description,
            'isPartOf' => ['@id' => self::websiteId()],
            'about' => ['@id' => self::organizationId()],
            'inLanguage' => config('seo.locale'),
        ];

        return $this;
    }

    /**
     * Add the breadcrumb trail.
     *
     * @param  list<array{name: string, url: string}>  $crumbs
     */
    public function breadcrumbs(array $crumbs): self
    {
        $this->nodes[] = [
            '@type' => 'BreadcrumbList',
            '@id' => ($crumbs === [] ? SeoData::absolute('/') : end($crumbs)['url']).'#breadcrumb',
            'itemListElement' => $this->positioned(
                $crumbs,
                fn (array $crumb, int $position): array => [
                    '@type' => 'ListItem',
                    'position' => $position,
                    'name' => $crumb['name'],
                    'item' => $crumb['url'],
                ],
            ),
        ];

        return $this;
    }

    /**
     * Add a listing of articles.
     *
     * @param  list<array{title: string, slug: string}>  $posts
     */
    public function postList(array $posts, string $url): self
    {
        $this->nodes[] = [
            '@type' => 'ItemList',
            '@id' => $url.'#postlist',
            'itemListOrder' => 'https://schema.org/ItemListOrderDescending',
            'numberOfItems' => count($posts),
            'itemListElement' => $this->positioned(
                $posts,
                fn (array $post, int $position): array => [
                    '@type' => 'ListItem',
                    'position' => $position,
                    'name' => $post['title'],
                    'url' => route('blog.show', $post['slug']),
                ],
            ),
        ];

        return $this;
    }

    /**
     * Add a single article.
     *
     * `BlogPosting` rather than the broader `Article`: it is the type Google
     * documents for blog content and it inherits everything `Article` defines.
     *
     * @param  array{
     *     title: string,
     *     description: string|null,
     *     url: string,
     *     image: string|null,
     *     published_at: string|null,
     *     modified_at: string|null,
     *     author: string|null,
     *     section: string|null,
     *     word_count: int|null,
     * }  $post
     */
    public function blogPosting(array $post): self
    {
        $node = array_filter([
            '@type' => 'BlogPosting',
            '@id' => $post['url'].'#article',
            'headline' => $post['title'],
            'description' => $post['description'],
            'url' => $post['url'],
            'datePublished' => $post['published_at'],
            'dateModified' => $post['modified_at'] ?? $post['published_at'],
            'articleSection' => $post['section'],
            'wordCount' => $post['word_count'],
            'inLanguage' => config('seo.locale'),
            'isPartOf' => ['@id' => self::websiteId()],
            'mainEntityOfPage' => ['@id' => $post['url'].'#webpage'],
            'publisher' => ['@id' => self::organizationId()],
            'author' => $post['author'] === null
                ? ['@id' => self::organizationId()]
                : ['@type' => 'Person', 'name' => $post['author']],
            'image' => $post['image'] === null ? null : [
                '@type' => 'ImageObject',
                'url' => $post['image'],
                'contentUrl' => $post['image'],
            ],
        ], fn (mixed $value): bool => $value !== null);

        $this->nodes[] = $node;

        return $this;
    }

    /**
     * Serialise the graph for a `<script type="application/ld+json">` element.
     *
     * `JSON_HEX_TAG` and `JSON_HEX_AMP` are the point of this method: without
     * them an admin who types `</script>` into a post title breaks out of the
     * script element. Slashes and unicode are left intact so the URLs and the
     * copy stay readable to anyone inspecting the page.
     */
    public function encode(): string
    {
        return json_encode([
            '@context' => 'https://schema.org',
            '@graph' => $this->nodes,
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /**
     * Map a list into schema.org list items, numbered from one.
     *
     * `position` is one-based in schema.org, and a zero-based list is the
     * single most common way to get an ItemList marked invalid.
     *
     * @template TItem of array<string, mixed>
     *
     * @param  list<TItem>  $items
     * @param  callable(TItem, int): array<string, mixed>  $callback
     * @return list<array<string, mixed>>
     */
    private function positioned(array $items, callable $callback): array
    {
        $mapped = [];

        foreach ($items as $index => $item) {
            $mapped[] = $callback($item, $index + 1);
        }

        return $mapped;
    }
}
