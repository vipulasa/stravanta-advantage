<?php

namespace App\Http\Responders;

use App\Http\Controllers\ContactSubmissionController;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Honeypot\SpamResponder\SpamResponder;

/**
 * Responds to spam with the same redirect a genuine submission produces.
 *
 * The package default, `BlankPageResponder`, returns an empty HTTP 200 with no
 * `X-Inertia` header, which the Inertia client treats as an invalid response
 * and surfaces as an error modal. Returning the ordinary success redirect keeps
 * the page working, gives a bot no signal that it was caught, and means a false
 * positive on a real visitor still looks like success.
 */
class InertiaSpamResponder implements SpamResponder
{
    public function respond(Request $request, Closure $next): RedirectResponse
    {
        Log::info('Discarded a spam contact submission.', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('status', ContactSubmissionController::CONFIRMATION_MESSAGE);
    }
}
