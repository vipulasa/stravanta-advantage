{{--
    Renders the `seo` prop into `<head>`.

    This is the fallback branch of `<x-inertia::head>`, so it only runs when
    Inertia has no SSR response to splice in — which covers a crawler hitting
    the site while the SSR process is down, and the whole test suite, where
    `phpunit.xml` disables SSR.

    Every tag carries `data-inertia` with the same key its React counterpart in
    `seo-head.tsx` uses. That attribute is how Inertia's head manager decides
    which elements it owns: on hydration it matches by key and *replaces* these
    tags, rather than appending a second copy of each one.
--}}
@props(['seo'])

<title data-inertia="">{{ $seo['title'] }}</title>
<meta data-inertia="description" name="description" content="{{ $seo['description'] }}">
<meta data-inertia="robots" name="robots" content="{{ $seo['robots'] }}">
<link data-inertia="canonical" rel="canonical" href="{{ $seo['canonical'] }}">

<meta data-inertia="og:site_name" property="og:site_name" content="{{ $seo['site_name'] }}">
<meta data-inertia="og:locale" property="og:locale" content="{{ $seo['og_locale'] }}">
<meta data-inertia="og:type" property="og:type" content="{{ $seo['type'] }}">
<meta data-inertia="og:title" property="og:title" content="{{ $seo['title'] }}">
<meta data-inertia="og:description" property="og:description" content="{{ $seo['description'] }}">
<meta data-inertia="og:url" property="og:url" content="{{ $seo['canonical'] }}">
@if ($seo['image'])
    <meta data-inertia="og:image" property="og:image" content="{{ $seo['image'] }}">
    <meta data-inertia="og:image:alt" property="og:image:alt" content="{{ $seo['image_alt'] }}">
    @if ($seo['image_width'] && $seo['image_height'])
        <meta data-inertia="og:image:width" property="og:image:width" content="{{ $seo['image_width'] }}">
        <meta data-inertia="og:image:height" property="og:image:height" content="{{ $seo['image_height'] }}">
    @endif
    @if ($seo['image_type'])
        <meta data-inertia="og:image:type" property="og:image:type" content="{{ $seo['image_type'] }}">
    @endif
@endif

@if ($seo['type'] === 'article')
    @if ($seo['published_time'])
        <meta data-inertia="article:published_time" property="article:published_time" content="{{ $seo['published_time'] }}">
    @endif
    @if ($seo['modified_time'])
        <meta data-inertia="article:modified_time" property="article:modified_time" content="{{ $seo['modified_time'] }}">
    @endif
    @if ($seo['author'])
        <meta data-inertia="article:author" property="article:author" content="{{ $seo['author'] }}">
    @endif
    @if ($seo['section'])
        <meta data-inertia="article:section" property="article:section" content="{{ $seo['section'] }}">
    @endif
@endif

<meta data-inertia="twitter:card" name="twitter:card" content="{{ $seo['twitter_card'] }}">
<meta data-inertia="twitter:title" name="twitter:title" content="{{ $seo['title'] }}">
<meta data-inertia="twitter:description" name="twitter:description" content="{{ $seo['description'] }}">
@if ($seo['image'])
    <meta data-inertia="twitter:image" name="twitter:image" content="{{ $seo['image'] }}">
    <meta data-inertia="twitter:image:alt" name="twitter:image:alt" content="{{ $seo['image_alt'] }}">
@endif
@if ($seo['twitter_site'])
    <meta data-inertia="twitter:site" name="twitter:site" content="{{ $seo['twitter_site'] }}">
@endif
@if ($seo['twitter_creator'])
    <meta data-inertia="twitter:creator" name="twitter:creator" content="{{ $seo['twitter_creator'] }}">
@endif

@if ($seo['schema'])
    {{-- Already encoded with JSON_HEX_TAG, so it cannot close this element. --}}
    <script data-inertia="schema" type="application/ld+json">{!! $seo['schema'] !!}</script>
@endif
