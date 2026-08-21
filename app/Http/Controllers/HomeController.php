<?php

namespace App\Http\Controllers;

use App\Support\Seo\SchemaGraph;
use App\Support\Seo\SeoData;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Show the marketing home page.
     *
     * A controller rather than `Route::inertia()` purely so the page can carry
     * its own SEO data; the component itself takes no props.
     */
    public function __invoke(): Response
    {
        $title = config('seo.default_title');
        $description = config('seo.default_description');
        $url = SeoData::homeUrl();

        return Inertia::render('welcome', [
            'seo' => SeoData::make($title, $description, $url)
                ->withSchema(
                    SchemaGraph::make()
                        ->siteIdentity()
                        ->serviceCatalog()
                        ->webPage($title, $description, $url),
                )
                ->toArray(),
        ]);
    }
}
