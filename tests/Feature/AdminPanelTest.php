<?php

use App\Enums\ServiceInterest;
use App\Filament\Resources\ContactSubmissions\ContactSubmissionResource;
use App\Models\ContactSubmission;
use App\Models\User;
use Filament\Facades\Filament;

/**
 * Resolve the admin panel's URL prefix from its configuration, so these tests
 * keep passing if the panel path is changed.
 */
function adminPath(string $suffix = ''): string
{
    return '/'.trim(Filament::getPanel('admin')->getPath().'/'.ltrim($suffix, '/'), '/');
}

test('guests are redirected from the admin panel to the login page', function () {
    $this->get(adminPath())->assertRedirect(adminPath('login'));
});

test('the admin login page is reachable', function () {
    $this->get(adminPath('login'))->assertOk();
});

test('an authenticated user can reach the admin dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get(adminPath())
        ->assertOk();
});

test('an authenticated user can reach the users resource', function () {
    $this->actingAs(User::factory()->create())
        ->get(adminPath('users'))
        ->assertOk();
});

test('users may access the admin panel', function () {
    expect(User::factory()->create()->canAccessPanel(Filament::getPanel('admin')))->toBeTrue();
});

test('an authenticated user can reach the contact submissions resource', function () {
    ContactSubmission::factory()->count(3)->create();

    $this->actingAs(User::factory()->create())
        ->get(adminPath('contact-submissions'))
        ->assertOk();
});

test('an authenticated user can view a contact submission', function () {
    $submission = ContactSubmission::factory()->create([
        'name' => 'Priya Wickramasinghe',
        'email' => 'priya@northwind.example',
        'company' => 'Northwind Exports',
        'service_interest' => ServiceInterest::BusinessAdvantageScan,
        'message' => 'Growth has outpaced our operating rhythm.',
    ]);

    // Asserts the infolist actually renders the enquiry, not merely that the
    // page returns 200 with an empty schema.
    $this->actingAs(User::factory()->create())
        ->get(adminPath('contact-submissions/'.$submission->getKey()))
        ->assertOk()
        ->assertSee('Priya Wickramasinghe')
        ->assertSee('priya@northwind.example')
        ->assertSee('Northwind Exports')
        ->assertSee('Business Advantage Scan')
        ->assertSee('Growth has outpaced our operating rhythm.');
});

test('contact submissions cannot be created or edited from the admin panel', function () {
    $submission = ContactSubmission::factory()->create();

    expect(ContactSubmissionResource::canCreate())->toBeFalse()
        ->and(ContactSubmissionResource::canEdit($submission))->toBeFalse();

    $user = User::factory()->create();

    $this->actingAs($user)->get(adminPath('contact-submissions/create'))->assertNotFound();
    $this->actingAs($user)
        ->get(adminPath('contact-submissions/'.$submission->getKey().'/edit'))
        ->assertNotFound();
});
