<?php

namespace App\Notifications;

use App\Filament\Resources\ContactSubmissions\ContactSubmissionResource;
use App\Models\ContactSubmission;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Alerts an admin that a new enquiry arrived.
 *
 * This notification must NOT implement `ShouldQueue`. It is already dispatched
 * asynchronously by `SendContactSubmissionNotifications`, and queueing it again
 * would push it onto the database queue, which has no worker running.
 */
class ContactSubmissionReceived extends Notification
{
    use Queueable;

    public function __construct(public ContactSubmission $submission) {}

    /**
     * Get the delivery channels.
     *
     * Database first, so an SMTP failure still leaves the in-panel alert.
     *
     * @return list<string>
     */
    public function via(User $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Build the mail representation.
     */
    public function toMail(User $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject(sprintf(
                'New enquiry: %s — %s',
                $this->submission->service_interest->getLabel(),
                $this->submission->name,
            ))
            ->replyTo($this->submission->email, $this->submission->name)
            ->greeting('A new enquiry has arrived.')
            ->line(sprintf('**Name:** %s', $this->submission->name))
            ->line(sprintf('**Email:** %s', $this->submission->email));

        if (filled($this->submission->company)) {
            $message->line(sprintf('**Company:** %s', $this->submission->company));
        }

        if (filled($this->submission->phone)) {
            $message->line(sprintf('**Phone:** %s', $this->submission->phone));
        }

        return $message
            ->line(sprintf('**Interested in:** %s', $this->submission->service_interest->getLabel()))
            ->line('---')
            ->line($this->submission->message)
            ->action('Open in the admin panel', $this->adminUrl());
    }

    /**
     * Build the Filament database representation.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(User $notifiable): array
    {
        return FilamentNotification::make()
            ->title(sprintf('New enquiry from %s', $this->submission->name))
            ->body(sprintf(
                '%s · %s',
                $this->submission->service_interest->getLabel(),
                $this->submission->email,
            ))
            ->icon(Heroicon::OutlinedEnvelope)
            ->actions([
                Action::make('view')
                    ->label('View enquiry')
                    ->url($this->adminUrl())
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }

    /**
     * Build an absolute URL to the enquiry inside the admin panel.
     *
     * The panel is named explicitly because this runs after the response has
     * been sent, when there is no "current" Filament panel to infer.
     */
    protected function adminUrl(): string
    {
        return ContactSubmissionResource::getUrl('view', ['record' => $this->submission], panel: 'admin');
    }
}
