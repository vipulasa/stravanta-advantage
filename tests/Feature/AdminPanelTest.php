<?php

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
