<?php

use App\Enums\Role;
use App\Enums\ServiceType;
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

test('registration screen preselects the cleaner role from the query string', function () {
    $response = $this->get(route('register', ['role' => 'cleaner']));

    $response->assertOk()->assertSee("role: 'cleaner'", false);
});

test('registration screen ignores an invalid role in the query string', function () {
    $response = $this->get(route('register', ['role' => "'});alert(1);({x:'"]));

    $response->assertOk()->assertSee("role: 'customer'", false);
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

test('a cleaner can register with the optional partner-application fields filled in', function () {
    Storage::fake('public');

    $response = $this->post(route('register.store'), [
        'name' => 'Marie Joseph',
        'email' => 'marie@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'cleaner',
        'phone' => '267-555-0100',
        'photo' => UploadedFile::fake()->image('marie.jpg'),
        'service_area' => 'Philadelphia, Bucks County',
        'experience' => '5 years of residential and office cleaning.',
        'services_offered' => [ServiceType::HouseCleaning->value, ServiceType::JanitorialServices->value],
        'availability' => 'Weekdays, evenings',
        'business_name' => 'Marie Cleaning Co.',
        'insurance_provider' => 'Acme Insurance',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $user = User::where('email', 'marie@example.com')->sole();

    expect($user->cleanerProfile->service_area)->toBe('Philadelphia, Bucks County')
        ->and($user->cleanerProfile->experience)->toBe('5 years of residential and office cleaning.')
        ->and($user->cleanerProfile->services_offered)->toBe([ServiceType::HouseCleaning->value, ServiceType::JanitorialServices->value])
        ->and($user->cleanerProfile->availability)->toBe('Weekdays, evenings')
        ->and($user->cleanerProfile->business_name)->toBe('Marie Cleaning Co.')
        ->and($user->cleanerProfile->insurance_provider)->toBe('Acme Insurance');
});

test('a cleaner can register leaving the partner-application fields blank', function () {
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

    expect($user->cleanerProfile->service_area)->toBeNull()
        ->and($user->cleanerProfile->services_offered)->toBe([]);
});
