<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();

    $user = User::where('email', 'test@example.com')->sole();

    expect($user->role)->toBe(Role::Customer)
        ->and($user->customerProfile)->not->toBeNull();
});

test('a cleaner can register with a phone number and profile photo', function () {
    Storage::fake('public');

    $response = $this->post(route('register.store'), [
        'name' => 'Marie Joseph',
        'email' => 'marie@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'cleaner',
        'phone' => '267-555-0100',
        'photo' => UploadedFile::fake()->image('marie.jpg'),
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $user = User::where('email', 'marie@example.com')->sole();

    expect($user->role)->toBe(Role::Cleaner)
        ->and($user->cleanerProfile->phone)->toBe('267-555-0100')
        ->and($user->cleanerProfile->agreement_signed)->toBeFalse();

    Storage::disk('public')->assertExists($user->cleanerProfile->photo_path);
});

test('a cleaner must provide a phone number and photo to register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Marie Joseph',
        'email' => 'marie@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'cleaner',
    ]);

    $response->assertSessionHasErrors(['phone', 'photo']);

    $this->assertGuest();
});
