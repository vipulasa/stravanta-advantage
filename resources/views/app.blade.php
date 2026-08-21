<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon-32x32.png" type="image/png" sizes="32x32">
        <link rel="icon" href="/favicon-16x16.png" type="image/png" sizes="16x16">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png" sizes="180x180">
        <link rel="manifest" href="/site.webmanifest">
        <meta name="theme-color" content="#071a48">

        {{-- Pages import their own stylesheets. The marketing site is plain CSS
             carried over from the approved template, so Tailwind's preflight is
             deliberately not loaded here — it resets heading font-weights and
             would alter the approved design. --}}
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        {{-- The slot is Inertia's non-SSR branch: it renders only when there
             is no SSR response to splice in. `x-seo` mirrors what
             `seo-head.tsx` produces, so an SSR outage still leaves crawlers
             and social scrapers a complete head. --}}
        <x-inertia::head>
            <x-seo :seo="$page['props']['seo']" />
        </x-inertia::head>
    </head>
    <body>
        <x-inertia::app />
    </body>
</html>
