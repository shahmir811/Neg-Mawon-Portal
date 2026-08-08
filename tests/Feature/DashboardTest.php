<?php

use App\Enums\Role;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('admins are redirected to the admin dashboard', function () {
    $user = User::factory()->create(['role' => Role::Admin]);
    $this->actingAs($user);

    $this->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
    $this->get(route('admin.dashboard'))->assertOk();
});

test('cleaners are redirected to the cleaner dashboard', function () {
    $user = User::factory()->create(['role' => Role::Cleaner]);
    $this->actingAs($user);

    $this->get(route('dashboard'))->assertRedirect(route('cleaner.dashboard'));
    $this->get(route('cleaner.dashboard'))->assertOk();
});

test('customers are redirected to the customer dashboard', function () {
    $user = User::factory()->create(['role' => Role::Customer]);
    $this->actingAs($user);

    $this->get(route('dashboard'))->assertRedirect(route('customer.dashboard'));
    $this->get(route('customer.dashboard'))->assertOk();
});

test('a customer cannot access the admin dashboard', function () {
    $user = User::factory()->create(['role' => Role::Customer]);
    $this->actingAs($user);

    $this->get(route('admin.dashboard'))->assertForbidden();
});

test('cleaner dashboard shows their agreement status', function () {
    $user = User::factory()->cleaner()->create();
    $user->cleanerProfile()->create([
        'agreement_photo_path' => 'agreements/pending.jpg',
        'agreement_signed' => false,
    ]);

    $this->actingAs($user);

    $this->get(route('cleaner.dashboard'))
        ->assertOk()
        ->assertSee('Pending review');
});
