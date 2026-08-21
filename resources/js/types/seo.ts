/**
 * The `seo` prop, built server-side by `App\Support\Seo\SeoData`.
 *
 * Both renderers — the Blade fallback and `SeoHead` — read this shape and
 * neither derives anything from it, so the tags a crawler sees before
 * hydration and the tags React manages afterwards are identical.
 */
export type Seo = {
    /** The complete `<title>`. Never suffixed on the client. */
    title: string;
    description: string;
    /** Absolute, self-referential, and query-string aware. */
    canonical: string;
    robots: string;
    /** The Open Graph object type, e.g. `website` or `article`. */
    type: string;
    site_name: string;
    /** BCP 47, for `<html lang>`. */
    locale: string;
    /** The underscored Open Graph variant, e.g. `en_GB`. */
    og_locale: string;
    image: string | null;
    image_alt: string | null;
    /** Null whenever the dimensions are not known, e.g. an uploaded image. */
    image_width: number | null;
    image_height: number | null;
    image_type: string | null;
    twitter_card: string;
    twitter_site: string | null;
    twitter_creator: string | null;
    /** ISO 8601. Article pages only. */
    published_time: string | null;
    modified_time: string | null;
    author: string | null;
    section: string | null;
    /** The schema.org graph, serialised on the server. */
    schema: string | null;
};
