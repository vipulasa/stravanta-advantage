<?php

use App\Enums\ServiceInterest;
use App\Filament\Resources\ContactSubmissions\ContactSubmissionResource;
use App\Http\Responders\InertiaSpamResponder;
use App\Mail\ContactEnquiryReceived;
use App\Models\ContactSubmission;
use App\Models\User;
use App\Notifications\ContactSubmissionReceived;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia;
use Spatie\Honeypot\Honeypot;

/**
 * Build a submission payload, optionally overriding or removing fields.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function validContactPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Dilani Perera',
        'email' => 'dilani@example.com',
        'company' => 'Northwind Exports',
        'phone' => '+94 77 123 4567',
        'service_interest' => ServiceInterest::PerformanceAccelerator->value,
        'message' => 'Delivery dates keep slipping and we cannot see why. Where would you start?',
    ], $overrides);
}

/**
 * Resolve the honeypot fields the way a freshly rendered page would.
 *
 * @return array<string, mixed>
 */
function honeypotFields(): array
{
    return app(Honeypot::class)->toArray();
}

test('the contact page renders the contact component', function () {
    $this->get(route('contact'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('contact'));
});

test('the marketing pages expose the honeypot configuration to the client', function (string $routeName) {
    $this->get(route($routeName))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('honeypot.nameFieldName')
            ->has('honeypot.validFromFieldName')
            ->has('honeypot.encryptedValidFrom')
            ->etc());
})->with(['home', 'contact']);

test('the marketing pages expose the three engagements', function (string $routeName) {
    $this->get(route($routeName))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('serviceInterests', 3)
            ->where('serviceInterests.0.label', 'Business Advantage Scan')
            ->where('serviceInterests.1.label', 'Performance Accelerator')
            ->where('serviceInterests.2.label', 'Executive Partner')
            ->etc());
})->with(['home', 'contact']);

test('a valid submission is persisted', function () {
    $this->post(route('contact.store'), validContactPayload())
        ->assertRedirect()
        ->assertSessionHas('status');

    $submission = ContactSubmission::query()->sole();

    expect($submission->name)->toBe('Dilani Perera')
        ->and($submission->email)->toBe('dilani@example.com')
        ->and($submission->company)->toBe('Northwind Exports')
        ->and($submission->phone)->toBe('+94 77 123 4567')
        ->and($submission->service_interest)->toBe(ServiceInterest::PerformanceAccelerator)
        ->and($submission->message)->toContain('Delivery dates keep slipping');
});

test('a valid submission notifies every user by database and mail', function () {
    Notification::fake();

    $users = User::factory()->count(3)->create();

    $this->post(route('contact.store'), validContactPayload())->assertRedirect();

    Notification::assertCount(3);

    foreach ($users as $user) {
        Notification::assertSentTo(
            $user,
            ContactSubmissionReceived::class,
            fn (ContactSubmissionReceived $notification, array $channels): bool => $channels === ['database', 'mail'],
        );
    }
});

test('a submission still succeeds when no users exist to notify', function () {
    expect(User::query()->count())->toBe(0);

    $this->post(route('contact.store'), validContactPayload())
        ->assertRedirect()
        ->assertSessionHas('status');

    expect(ContactSubmission::query()->count())->toBe(1);
});

test('the optional company and phone may be omitted', function () {
    $this->post(route('contact.store'), validContactPayload([
        'company' => '',
        'phone' => '',
    ]))->assertRedirect()->assertSessionHasNoErrors();

    $submission = ContactSubmission::query()->sole();

    expect($submission->company)->toBeNull()
        ->and($submission->phone)->toBeNull();
});

test('the honeypot fields are never persisted to the model', function () {
    $honeypot = honeypotFields();

    $this->post(route('contact.store'), validContactPayload([
        $honeypot['nameFieldName'] => '',
        $honeypot['validFromFieldName'] => $honeypot['encryptedValidFrom'],
    ]))->assertRedirect();

    expect(ContactSubmission::query()->sole()->getAttributes())
        ->not->toHaveKey($honeypot['nameFieldName'])
        ->not->toHaveKey($honeypot['validFromFieldName']);
});

test('the required fields must be provided', function (string $field) {
    $this->from(route('contact'))
        ->post(route('contact.store'), validContactPayload([$field => '']))
        ->assertRedirect(route('contact'))
        ->assertSessionHasErrors($field);

    expect(ContactSubmission::query()->count())->toBe(0);
})->with(['name', 'email', 'service_interest', 'message']);

test('the email address must be well formed', function (string $email) {
    $this->from(route('contact'))
        ->post(route('contact.store'), validContactPayload(['email' => $email]))
        ->assertSessionHasErrors('email');

    expect(ContactSubmission::query()->count())->toBe(0);
})->with(['not-an-email', '@example.com', 'spaces in@example.com', 'two@@example.com']);

test('an unknown engagement is rejected', function () {
    $this->from(route('contact'))
        ->post(route('contact.store'), validContactPayload([
            'service_interest' => 'gold-plated-retainer',
        ]))
        ->assertSessionHasErrors('service_interest');

    expect(ContactSubmission::query()->count())->toBe(0);
});

test('the message length is bounded', function (string $message) {
    $this->from(route('contact'))
        ->post(route('contact.store'), validContactPayload(['message' => $message]))
        ->assertSessionHasErrors('message');

    expect(ContactSubmission::query()->count())->toBe(0);
})->with([
    'too short' => 'Hi there',
    'too long' => fn () => str_repeat('a', 5001),
]);

test('overlong values are rejected', function (string $field, int $limit) {
    $this->from(route('contact'))
        ->post(route('contact.store'), validContactPayload([
            $field => str_repeat('a', $limit + 1),
        ]))
        ->assertSessionHasErrors($field);
})->with([
    ['name', 255],
    ['company', 255],
    ['phone', 50],
]);

/*
|--------------------------------------------------------------------------
| Honeypot
|--------------------------------------------------------------------------
|
| Honeypot protection is disabled for the suite via phpunit.xml so it cannot
| break the tests above. `SpamProtection::check()` reads `honeypot.enabled` at
| call time, so these tests switch it back on individually. The responder,
| however, is bound at boot and can only be changed in config/honeypot.php.
|
*/

test('a submission with a filled honeypot field is silently discarded', function () {
    config()->set('honeypot.enabled', true);

    $honeypot = honeypotFields();

    $this->from(route('contact'))
        ->post(route('contact.store'), validContactPayload([
            $honeypot['nameFieldName'] => 'https://buy-cheap-things.example',
            $honeypot['validFromFieldName'] => $honeypot['encryptedValidFrom'],
        ]))
        // A redirect, not the package default blank 200, which the Inertia
        // client would surface as an error.
        ->assertRedirect(route('contact'))
        ->assertSessionHasNoErrors()
        // Indistinguishable from success, so a bot learns nothing.
        ->assertSessionHas('status');

    expect(ContactSubmission::query()->count())->toBe(0);
});

test('a submission made faster than the honeypot window is discarded', function () {
    config()->set('honeypot.enabled', true);
    // Set well above any plausible test runtime; with the real one second
    // value a slow CI runner could cross the threshold and flake.
    config()->set('honeypot.amount_of_seconds', 600);

    $honeypot = honeypotFields();

    $this->post(route('contact.store'), validContactPayload([
        $honeypot['nameFieldName'] => '',
        $honeypot['validFromFieldName'] => $honeypot['encryptedValidFrom'],
    ]))->assertRedirect();

    expect(ContactSubmission::query()->count())->toBe(0);
});

test('a submission that omits the honeypot fields entirely is discarded', function () {
    config()->set('honeypot.enabled', true);

    // The package default lets a bot bypass the check by simply not sending the
    // fields; `honeypot_fields_required_for_all_forms` closes that hole.
    expect(config('honeypot.honeypot_fields_required_for_all_forms'))->toBeTrue();

    $this->post(route('contact.store'), validContactPayload())->assertRedirect();

    expect(ContactSubmission::query()->count())->toBe(0);
});

test('a genuine submission passes the honeypot', function () {
    config()->set('honeypot.enabled', true);
    config()->set('honeypot.amount_of_seconds', 0);

    $honeypot = honeypotFields();

    $this->post(route('contact.store'), validContactPayload([
        $honeypot['nameFieldName'] => '',
        $honeypot['validFromFieldName'] => $honeypot['encryptedValidFrom'],
    ]))->assertRedirect()->assertSessionHasNoErrors();

    expect(ContactSubmission::query()->count())->toBe(1);
});

test('the spam responder is configured for Inertia rather than the package default', function () {
    expect(config('honeypot.respond_to_spam_with'))
        ->toBe(InertiaSpamResponder::class);
});

/*
|--------------------------------------------------------------------------
| Acknowledgement to the sender
|--------------------------------------------------------------------------
*/

test('the person who enquired is sent a thank you email', function () {
    Mail::fake();

    $this->post(route('contact.store'), validContactPayload())->assertRedirect();

    Mail::assertSent(
        ContactEnquiryReceived::class,
        fn (ContactEnquiryReceived $mail): bool => $mail->hasTo('dilani@example.com')
            && $mail->submission->is(ContactSubmission::query()->sole()),
    );
});

test('the thank you email promises a reply within one business day', function () {
    $submission = ContactSubmission::factory()->create([
        'name' => 'Dilani Perera',
        'service_interest' => ServiceInterest::ExecutivePartner,
    ]);

    $rendered = (new ContactEnquiryReceived($submission))->render();

    expect($rendered)->toContain('Dilani Perera')
        ->toContain('within one')
        ->toContain('business day')
        ->toContain('Executive Partner');
});

test('a failure acknowledging the sender does not stop the admin alerts', function () {
    Notification::fake();
    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP is down'));

    $user = User::factory()->create();

    $this->post(route('contact.store'), validContactPayload())
        ->assertRedirect()
        ->assertSessionHas('status');

    expect(ContactSubmission::query()->count())->toBe(1);
    Notification::assertSentTo($user, ContactSubmissionReceived::class);
});

test('the admin notification renders for both channels', function () {
    // Rendered for real rather than faked: `Notification::fake()` never builds
    // the message, so a broken route inside it would otherwise go unnoticed
    // until a live submission failed.
    $submission = ContactSubmission::factory()->create(['name' => 'Dilani Perera']);
    $admin = User::factory()->create();

    $notification = new ContactSubmissionReceived($submission);

    $database = $notification->toDatabase($admin);
    expect($database)->toBeArray()
        ->and($database['title'] ?? null)->toContain('Dilani Perera');

    $mail = $notification->toMail($admin);
    expect($mail->subject)->toContain('Dilani Perera');

    // The admin email and the in-panel notification must both deep link to the
    // read-only view page; the edit page no longer exists, so an /edit link
    // would 404.
    $viewUrl = ContactSubmissionResource::getUrl('view', ['record' => $submission], panel: 'admin');

    expect($viewUrl)->toContain('contact-submissions')
        ->and($viewUrl)->not->toContain('/edit');

    $mailActionUrl = collect($mail->actionUrl)->implode('');
    expect($mailActionUrl)->toBe($viewUrl)
        ->and($mailActionUrl)->not->toContain('/edit');

    $databaseActionUrls = collect($database['actions'] ?? [])->pluck('url')->filter();
    expect($databaseActionUrls)->not->toBeEmpty();
    $databaseActionUrls->each(fn (string $url) => expect($url)->toBe($viewUrl));
});
