<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Serve robots.txt.
     *
     * A route rather than a file in `public/` so the `Sitemap:` line carries
     * the real domain — the directive has to be an absolute URL, and hardcoding
     * one into a static file makes it wrong in every environment but the one it
     * was written for.
     *
     * Non-production environments disallow everything. A staging copy that is
     * reachable is a staging copy that gets crawled.
     */
    public function __invoke(): Response
    {
        $lines = ['User-agent: *'];

        if (app()->environment('production')) {
            foreach (config('seo.disallowed_paths') as $path) {
                $lines[] = 'Disallow: '.$path;
            }

            $lines[] = 'Allow: /';
        } else {
            $lines[] = 'Disallow: /';
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.route('sitemap');

        return response(implode("\n", $lines)."\n")
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }
}
