<?php

namespace App\Jobs;

use App\Mail\ContactEnquiryReceived;
use App\Models\ContactSubmission;
use App\Models\User;
use App\Notifications\ContactSubmissionReceived;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Acknowledges an enquiry to its sender and alerts every admin.
 *
 * Dispatched with `afterResponse()`, so it runs in process once the visitor's
 * response has been flushed: no queue worker is required, and a mail outage can
 * never fail the submission that has already been saved. It implements
 * `ShouldQueue` so that dropping `afterResponse()` is all that is needed to move
 * it onto a real worker later.
 */
class SendContactSubmissionNotifications implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public ContactSubmission $submission) {}

    /**
     * Get the retry backoff, used once this runs on a real queue worker.
     *
     * @return list<int>
     */
    public function backoff(): array
    {
        return [10, 60];
    }

    public function handle(): void
    {
        $this->acknowledgeToSender();

        $recipients = User::all();

        if ($recipients->isEmpty()) {
            Log::warning('A contact submission was received but no users exist to notify.', [
                'contact_submission_id' => $this->submission->getKey(),
            ]);

            return;
        }

        foreach ($recipients as $recipient) {
            try {
                $recipient->notify(new ContactSubmissionReceived($this->submission));
            } catch (Throwable $exception) {
                // Caught per recipient so one bad address cannot deny the rest
                // their alert, and so nothing escapes into the terminate phase.
                Log::error('Failed to notify a user about a contact submission.', [
                    'contact_submission_id' => $this->submission->getKey(),
                    'user_id' => $recipient->getKey(),
                    'exception' => $exception->getMessage(),
                ]);
            }
        }
    }

    /**
     * Send the automatic thank you to the person who made the enquiry.
     *
     * Isolated from the admin alerts so a bounce on the visitor's address
     * cannot stop the team being told about their enquiry.
     */
    protected function acknowledgeToSender(): void
    {
        try {
            Mail::to($this->submission->email, $this->submission->name)
                ->send(new ContactEnquiryReceived($this->submission));
        } catch (Throwable $exception) {
            Log::error('Failed to acknowledge a contact submission to its sender.', [
                'contact_submission_id' => $this->submission->getKey(),
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
