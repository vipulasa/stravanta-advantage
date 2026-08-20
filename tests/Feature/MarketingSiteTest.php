<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('the marketing home page renders the STRAVANTA welcome component', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('welcome'));
});

test('the marketing site exposes no public authentication routes', function (string $path) {
    $this->get($path)->assertNotFound();
})->with([
    'login',
    'register',
    'forgot-password',
    'settings/profile',
    'settings/security',
    'settings/appearance',
    'dashboard',
    'two-factor-challenge',
    '.well-known/passkey-endpoints',
]);

test('registration is not possible through a public endpoint', function () {
    $this->post('/register', [
        'name' => 'Intruder',
        'email' => 'intruder@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();

    expect(User::query()->count())->toBe(0);
});
