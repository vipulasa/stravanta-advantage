<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site Identity
    |--------------------------------------------------------------------------
    |
    | The values every page falls back to. Individual pages override the title,
    | description and image; everything else here is emitted verbatim on every
    | response.
    |
    */

    'site_name' => env('APP_NAME', 'STRAVANTA Advisory'),

    'default_title' => 'STRAVANTA Advisory | Turn Ambition Into Advantage',

    'default_description' => 'Operator-led strategy, operations, AI and transformation advisory for ambitious companies in Sri Lanka and Europe.',

    /*
     * BCP 47 for `<html lang>`, and the underscored Open Graph variant. Open
     * Graph will not accept a bare `en`, so the two are kept separately rather
     * than derived from one another.
     */
    'locale' => 'en',

    'og_locale' => 'en_GB',

    /*
    |--------------------------------------------------------------------------
    | Default Share Image
    |--------------------------------------------------------------------------
    |
    | Used for Open Graph and X (Twitter) cards on any page that has no image
    | of its own. Open Graph wants 1200x630 (1.91:1); anything else is cropped
    | by the platform. Swap the file and the dimensions together.
    |
    */

    'image' => [
        'path' => '/images/og-default.png',
        'width' => 1200,
        'height' => 630,
        'type' => 'image/jpeg',
        'alt' => 'STRAVANTA Advisory — building stronger tomorrows',
    ],

    /*
    |--------------------------------------------------------------------------
    | Crawler Directives
    |--------------------------------------------------------------------------
    |
    | The default `robots` meta value. Non-production environments are forced
    | to `noindex` by SeoData so a staging copy cannot be indexed, regardless
    | of what is set here.
    |
    */

    'robots' => 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',

    /*
     * Paths kept out of robots.txt and the sitemap. `_admin` is the Filament
     * panel path set in AdminPanelProvider.
     */
    'disallowed_paths' => [
        '/_admin',
        '/storage/',
        '/build/',
    ],

    /*
    |--------------------------------------------------------------------------
    | X (Twitter) Cards
    |--------------------------------------------------------------------------
    |
    | `site` and `creator` are the @handles for the brand and the author. Both
    | are omitted from the markup while null — an empty handle renders the card
    | attribution as a dead link.
    |
    */

    'twitter' => [
        'card' => 'summary_large_image',
        'site' => null,
        'creator' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Organization
    |--------------------------------------------------------------------------
    |
    | Feeds the schema.org Organization node that appears in the JSON-LD graph
    | of every page. Only add `same_as` profiles that genuinely exist and are
    | controlled by the business — a fabricated profile is treated as spam.
    |
    */

    'organization' => [
        'name' => 'STRAVANTA Advisory',
        'legal_name' => 'STRAVANTA Advisory',
        'slogan' => 'Build smarter. Operate better. Grow faster.',
        'description' => 'Operator-led advisory helping ambitious companies convert strategy into measurable growth through better operations, practical AI and disciplined execution.',
        'email' => 'hello@stravantaadvisory.com',
        'logo' => [
            'path' => '/images/stravanta-logo.png',
            'width' => 600,
            'height' => 200,
        ],
        'area_served' => ['Sri Lanka', 'Europe'],
        'knows_about' => [
            'Operational excellence',
            'Business transformation',
            'Applied artificial intelligence',
            'Delivery governance',
            'Executive advisory',
        ],
        'same_as' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Catalogue
    |--------------------------------------------------------------------------
    |
    | Emitted as the Organization's `hasOfferCatalog` on the home page. These
    | mirror the service cards in `resources/js/pages/welcome.tsx`; when the
    | offering changes, both have to be updated.
    |
    */

    'services' => [
        [
            'name' => 'Business Advantage Scan',
            'description' => 'Find the operational bottlenecks and practical AI opportunities that can create the greatest measurable return.',
            'duration' => 'P2W',
        ],
        [
            'name' => 'Performance Accelerator',
            'description' => 'Build a practical operating system that strengthens priorities, accountability, visibility and delivery predictability.',
            'duration' => 'P8W',
        ],
        [
            'name' => 'Executive Partner',
            'description' => 'Add senior operations and delivery leadership without the cost or commitment of a full-time director.',
            'duration' => 'P3M',
        ],
    ],

];
