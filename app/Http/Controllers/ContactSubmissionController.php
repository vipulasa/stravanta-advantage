<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactSubmissionRequest;
use App\Jobs\SendContactSubmissionNotifications;
use App\Models\ContactSubmission;
use Illuminate\Http\RedirectResponse;

class ContactSubmissionController extends Controller
{
    /**
     * The confirmation shown after an enquiry is accepted.
     *
     * Shared with the spam responder so a discarded submission is
     * indistinguishable from a genuine one.
     */
    public const CONFIRMATION_MESSAGE = 'Thanks — your enquiry is with us. We reply within one business day.';

    /**
     * Store an enquiry and alert the team.
     */
    public function __invoke(StoreContactSubmissionRequest $request): RedirectResponse
    {
        $submission = ContactSubmission::create($request->validated());

        SendContactSubmissionNotifications::dispatch($submission)->afterResponse();

        return back()->with('status', self::CONFIRMATION_MESSAGE);
    }
}
