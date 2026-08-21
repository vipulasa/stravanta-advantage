import { Head, usePage } from '@inertiajs/react';
import type { Seo } from '@/types/seo';

/**
 * Renders the server-built `seo` prop into `<head>`.
 *
 * Reads the prop rather than taking it as an argument: `seo` is shared from
 * `HandleInertiaRequests` and overridden per page, so a page only has to drop
 * `<SeoHead />` in and the right tags follow.
 *
 * Each `head-key` matches the `data-inertia` value the Blade fallback in
 * `resources/views/components/seo.blade.php` writes for the same tag. On
 * hydration Inertia matches on that key and replaces the server-rendered tag
 * instead of appending a duplicate.
 */
export default function SeoHead() {
    const { seo } = usePage<{ seo?: Seo }>().props;

    // `seo` is a shared prop, so it is always present in practice. Guarding
    // anyway because this component runs inside the SSR bundle: reading a
    // field off an undefined prop there throws, Inertia sees a failed render,
    // and the visitor gets a blank page instead of a page missing a meta tag.
    if (!seo) {
        return null;
    }

    return (
        <Head>
            <title>{seo.title}</title>
            <meta
                head-key="description"
                name="description"
                content={seo.description}
            />
            <meta head-key="robots" name="robots" content={seo.robots} />
            <link head-key="canonical" rel="canonical" href={seo.canonical} />

            <meta
                head-key="og:site_name"
                property="og:site_name"
                content={seo.site_name}
            />
            <meta
                head-key="og:locale"
                property="og:locale"
                content={seo.og_locale}
            />
            <meta head-key="og:type" property="og:type" content={seo.type} />
            <meta head-key="og:title" property="og:title" content={seo.title} />
            <meta
                head-key="og:description"
                property="og:description"
                content={seo.description}
            />
            <meta head-key="og:url" property="og:url" content={seo.canonical} />

            {seo.image && (
                <meta
                    head-key="og:image"
                    property="og:image"
                    content={seo.image}
                />
            )}
            {seo.image && seo.image_alt && (
                <meta
                    head-key="og:image:alt"
                    property="og:image:alt"
                    content={seo.image_alt}
                />
            )}
            {seo.image && seo.image_width && seo.image_height && (
                <meta
                    head-key="og:image:width"
                    property="og:image:width"
                    content={String(seo.image_width)}
                />
            )}
            {seo.image && seo.image_width && seo.image_height && (
                <meta
                    head-key="og:image:height"
                    property="og:image:height"
                    content={String(seo.image_height)}
                />
            )}
            {seo.image && seo.image_type && (
                <meta
                    head-key="og:image:type"
                    property="og:image:type"
                    content={seo.image_type}
                />
            )}

            {seo.type === 'article' && seo.published_time && (
                <meta
                    head-key="article:published_time"
                    property="article:published_time"
                    content={seo.published_time}
                />
            )}
            {seo.type === 'article' && seo.modified_time && (
                <meta
                    head-key="article:modified_time"
                    property="article:modified_time"
                    content={seo.modified_time}
                />
            )}
            {seo.type === 'article' && seo.author && (
                <meta
                    head-key="article:author"
                    property="article:author"
                    content={seo.author}
                />
            )}
            {seo.type === 'article' && seo.section && (
                <meta
                    head-key="article:section"
                    property="article:section"
                    content={seo.section}
                />
            )}

            <meta
                head-key="twitter:card"
                name="twitter:card"
                content={seo.twitter_card}
            />
            <meta
                head-key="twitter:title"
                name="twitter:title"
                content={seo.title}
            />
            <meta
                head-key="twitter:description"
                name="twitter:description"
                content={seo.description}
            />
            {seo.image && (
                <meta
                    head-key="twitter:image"
                    name="twitter:image"
                    content={seo.image}
                />
            )}
            {seo.image && seo.image_alt && (
                <meta
                    head-key="twitter:image:alt"
                    name="twitter:image:alt"
                    content={seo.image_alt}
                />
            )}
            {seo.twitter_site && (
                <meta
                    head-key="twitter:site"
                    name="twitter:site"
                    content={seo.twitter_site}
                />
            )}
            {seo.twitter_creator && (
                <meta
                    head-key="twitter:creator"
                    name="twitter:creator"
                    content={seo.twitter_creator}
                />
            )}

            {seo.schema && (
                /* Encoded server-side with JSON_HEX_TAG, so admin-authored
                   copy inside the graph cannot close this element early. */
                <script
                    head-key="schema"
                    type="application/ld+json"
                    dangerouslySetInnerHTML={{ __html: seo.schema }}
                />
            )}
        </Head>
    );
}
