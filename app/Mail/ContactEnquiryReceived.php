<?php

namespace App\Mail;

use App\Models\ContactSubmission;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * The automatic acknowledgement sent to the person who made the enquiry.
 */
class ContactEnquiryReceived extends Mailable
{
    public function __construct(public ContactSubmission $submission) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thanks for getting in touch — STRAVANTA Advisory',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.contact.enquiry-received',
        );
    }
}
