<x-mail::message>
# Thank you, {{ $submission->name }}.

We have your enquiry and a member of the team will come back to you **within one
business day**, usually sooner.

Here is what you sent us, for your records:

<x-mail::panel>
**Interested in:** {{ $submission->service_interest->getLabel() }}

{{ $submission->message }}
</x-mail::panel>

In the meantime, if anything changes or you would like to add context, simply
reply to this email — it reaches us directly.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
