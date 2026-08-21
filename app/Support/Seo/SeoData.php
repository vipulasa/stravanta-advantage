<?php

namespace App\Support\Seo;

use Illuminate\Http\Request;

/**
 * Everything that ends up inside `<head>` for one page.
 *
 * Built server-side and handed to the front end as a single `seo` prop. Two
 * renderers consume it — `resources/views/components/seo.blade.php` when SSR is
 * not in play, and `resources/js/components/seo-head.tsx` once React takes
 * over — and neither of them computes anything, so the two can never disagree
 * about what a page's tags should be.
 *
 * @phpstan-type SeoArray array{
 *     title: string,
 *     description: string,
 *     canonical: string,
 *     robots: string,
 *     type: string,
 *     site_name: string,
 *     locale: string,
 *     og_locale: string,
 *     image: string|null,
 *     image_alt: string|null,
 *     image_width: int|null,
 *     image_height: int|null,
 *     image_type: string|null,
 *     twitter_card: string,
 *     twitter_site: string|null,
 *     twitter_creator: string|null,
 *     published_time: string|null,
 *     modified_time: string|null,
 *     author: string|null,
 *     section: string|null,
 *     schema: string|null,
 * }
 */
class SeoData
{
    /**
     * The schema.org graph, serialised.
     *
     * Held as a string rather than an array so it is encoded exactly once, on
     * the server, with the escaping that makes it safe to drop straight into a
     * `<script>` element. Admin-authored titles reach this graph, and an
     * unescaped `</script>` in one of them would otherwise close the tag early.
     */
    private ?string $schema = null;

    private ?string $publishedTime = null;

    private ?string $modifiedTime = null;

    private ?string $author = null;

    private ?string $section = null;

    private ?string $robots = null;

    private string $type = 'website';

    private ?string $image = null;

    private ?string $imageAlt = null;

    private ?int $imageWidth = null;

    private ?int $imageHeight = null;

    private ?string $imageType = null;

    final public function __construct(
        private string $title,
        private string $description,
        private string $canonical,
    ) {
        $image = config('seo.image');

        $this->image = self::absolute($image['path']);
        $this->imageAlt = $image['alt'];
        $this->imageWidth = $image['width'];
        $this->imageHeight = $image['height'];
        $this->imageType = $image['type'];
    }

    /**
     * Start from a page's own title, description and URL.
     *
     * Titles are passed whole and are never suffixed here: the marketing pages
     * carry titles copied verbatim from the approved template, and appending a
     * site name to them would change approved copy.
     */
    public static function make(string $title, string $description, string $canonical): static
    {
        return new static($title, $description, $canonical);
    }

    /**
     * The tags used when a page supplies none of its own.
     *
     * Shared from `HandleInertiaRequests`, so a page that forgets its `seo`
     * prop still renders a complete, canonical-correct head rather than an
     * empty one.
     */
    public static function default(Request $request): static
    {
        return static::make(
            config('seo.default_title'),
            config('seo.default_description'),
            self::canonicalFor($request),
        )->withSchema(SchemaGraph::make()->siteIdentity()->webPage(
            config('seo.default_title'),
            config('seo.default_description'),
            self::canonicalFor($request),
        ));
    }

    /**
     * Build the canonical URL for the current request.
     *
     * Self-referential, but only over the parameters a page says are part of
     * its identity: `?category=` and `?page=` each produce a genuinely
     * different listing, so collapsing those onto the bare path would tell
     * Google those pages do not exist.
     *
     * Everything else is dropped. Shared links arrive carrying `utm_source`,
     * `fbclid` and `gclid`, and echoing those back into the canonical declares
     * every campaign variant a page in its own right — which is precisely the
     * duplicate content a canonical exists to prevent.
     *
     * @param  list<string>  $allowed  Query keys that identify the page.
     */
    public static function canonicalFor(Request $request, array $allowed = []): string
    {
        $query = array_filter(
            $request->query(),
            fn (string $key): bool => in_array($key, $allowed, true),
            ARRAY_FILTER_USE_KEY,
        );

        ksort($query);

        $url = self::absolute($request->getPathInfo());

        return $query === [] ? $url : $url.'?'.http_build_query($query);
    }

    /**
     * The canonical form of the home page URL.
     *
     * `route('home')` produces the bare origin with no trailing slash, which
     * is a second spelling of the same page: browsers, the sitemap and the
     * schema graph all use the trailing-slash form. Everything that needs to
     * name the home page goes through here so only one spelling is ever
     * published.
     */
    public static function homeUrl(): string
    {
        return self::absolute('/');
    }

    /**
     * Turn a root-relative path into an absolute URL on the configured domain.
     *
     * Absolute URLs already pass straight through, which is what media library
     * image URLs are.
     */
    public static function absolute(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return rtrim(config('app.url'), '/').'/'.ltrim($path, '/');
    }

    /**
     * Set the Open Graph object type, e.g. `article` for a blog post.
     */
    public function withType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Override the share image.
     *
     * Passing null falls back to the configured default rather than dropping
     * the image, so a post without a featured image still shares as a card.
     */
    public function withImage(?string $url, ?string $alt = null, ?int $width = null, ?int $height = null, ?string $mimeType = null): static
    {
        if ($url === null) {
            return $this;
        }

        $this->image = self::absolute($url);
        $this->imageAlt = $alt ?? $this->title;
        // Media library conversions have no dimensions known here, and a wrong
        // og:image:width is worse than none: platforms trust it and reserve the
        // stated aspect ratio before the file is fetched.
        $this->imageWidth = $width;
        $this->imageHeight = $height;
        $this->imageType = $mimeType;

        return $this;
    }

    /**
     * Attach the article-specific Open Graph properties.
     */
    public function withArticle(?string $publishedTime, ?string $modifiedTime, ?string $author, ?string $section): static
    {
        $this->publishedTime = $publishedTime;
        $this->modifiedTime = $modifiedTime;
        $this->author = $author;
        $this->section = $section;

        return $this;
    }

    /**
     * Override the crawler directives, e.g. to keep a page out of the index.
     */
    public function withRobots(string $robots): static
    {
        $this->robots = $robots;

        return $this;
    }

    /**
     * Attach the page's schema.org graph.
     */
    public function withSchema(SchemaGraph $graph): static
    {
        $this->schema = $graph->encode();

        return $this;
    }

    /**
     * Resolve the crawler directives for this page.
     *
     * Anything that is not production is forced out of the index. Staging and
     * local copies are routinely reachable, and a `noindex` that depends on
     * someone remembering to set it is a `noindex` that eventually is not set.
     */
    private function robots(): string
    {
        if (! app()->environment('production')) {
            return 'noindex, nofollow';
        }

        return $this->robots ?? config('seo.robots');
    }

    /**
     * Flatten to the `seo` prop.
     *
     * @return SeoArray
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'canonical' => $this->canonical,
            'robots' => $this->robots(),
            'type' => $this->type,
            'site_name' => config('seo.site_name'),
            'locale' => config('seo.locale'),
            'og_locale' => config('seo.og_locale'),
            'image' => $this->image,
            'image_alt' => $this->imageAlt,
            'image_width' => $this->imageWidth,
            'image_height' => $this->imageHeight,
            'image_type' => $this->imageType,
            'twitter_card' => config('seo.twitter.card'),
            'twitter_site' => config('seo.twitter.site'),
            'twitter_creator' => config('seo.twitter.creator'),
            'published_time' => $this->publishedTime,
            'modified_time' => $this->modifiedTime,
            'author' => $this->author,
            'section' => $this->section,
            'schema' => $this->schema,
        ];
    }
}
