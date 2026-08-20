<?php

use App\Models\User;

test('guests are redirected from the admin panel to the Filament login page', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

test('the Filament login page is reachable', function () {
    $this->get('/admin/login')->assertOk();
});

test('an authenticated user can reach the admin dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk();
});

test('users may access the admin panel', function () {
    $panel = Filament\Facades\Filament::getPanel('admin');

    expect(User::factory()->create()->canAccessPanel($panel))->toBeTrue();
});
