---
paths:
  - 'app/Support/Seo/**'
---

# Seo

## SEO tags are built server-side and rendered twice
`App\Support\Seo\SeoData` builds one `seo` array prop per page (shared default in `HandleInertiaRequests`, overridden by each controller). Two dumb renderers consume it and must stay identical: `resources/views/components/seo.blade.php` (Inertia's non-SSR fallback, inside `<x-inertia::head>`) and `resources/js/components/seo-head.tsx`. Neither may compute anything — add new tags to both, or the SSR and non-SSR heads diverge.

Every Blade tag needs `data-inertia="<key>"` matching the React tag's `head-key`. That attribute is how Inertia's client head manager claims an element; without a match it appends a second copy instead of replacing.

JSON-LD is encoded once in `SchemaGraph::encode()` with `JSON_HEX_TAG|JSON_HEX_AMP` and passed as a string. Never build the graph client-side — admin-authored post titles reach it and an unescaped `</script>` breaks out of the element.

`config/seo.php` holds site identity, the default OG image and the service catalogue. `seo.services` mirrors the cards in `resources/js/pages/welcome.tsx` — change both together.
