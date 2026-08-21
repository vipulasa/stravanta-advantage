<?php

namespace App\Http\Controllers;

use App\Support\Seo\SchemaGraph;
use App\Support\Seo\SeoData;
use Inertia\Inertia;
use Inertia\Response;

class ContactPageController extends Controller
{
    /**
     * Show the enquiry page.
     *
     * The form itself posts to {@see ContactSubmissionController}; this action
     * only renders the page and its SEO data.
     */
    public function __invoke(): Response
    {
        $title = 'Contact STRAVANTA Advisory';
        $description = 'Start a conversation about your priorities, constraints and the business outcome that matters most. Operator-led advisory across Sri Lanka and Europe.';
        $url = route('contact');

        return Inertia::render('contact', [
            'seo' => SeoData::make($title, $description, $url)
                ->withSchema(
                    SchemaGraph::make()
                        ->siteIdentity()
                        ->webPage($title, $description, $url, 'ContactPage')
                        ->breadcrumbs([
                            ['name' => 'Home', 'url' => SeoData::homeUrl()],
                            ['name' => 'Contact', 'url' => $url],
                        ]),
                )
                ->toArray(),
        ]);
    }
}
