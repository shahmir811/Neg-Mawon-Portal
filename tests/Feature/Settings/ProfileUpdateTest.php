<?php

use App\Models\CleanerProfile;
use App\Models\CleaningJob;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get(route('profile.edit'))->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual('Test User');
    expect($user->email)->toEqual('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('cleaner can update their profile photo', function () {
    Storage::fake('public');

    $user = User::factory()->cleaner()->create();
    CleanerProfile::factory()->for($user)->create(['photo_path' => 'cleaners/old.jpg']);

    $this->actingAs($user);

    $photo = UploadedFile::fake()->image('photo.jpg');

    $response = Livewire::test('pages::settings.profile')
        ->set('photo', $photo)
        ->call('updatePhoto');

    $response->assertHasNoErrors();

    $path = $user->cleanerProfile->fresh()->photo_path;

    expect($path)->not->toBeNull();
    Storage::disk('public')->assertExists($path);
    Storage::disk('public')->assertMissing('cleaners/old.jpg');
});

test('customer cannot update a profile photo', function () {
    $user = User::factory()->customer()->create();

    $this->actingAs($user);

    $photo = UploadedFile::fake()->image('photo.jpg');

    Livewire::test('pages::settings.profile')
        ->set('photo', $photo)
        ->call('updatePhoto')
        ->assertForbidden();
});

test('cleaner can submit their agreement photo for review', function () {
    Storage::fake('public');

    $user = User::factory()->cleaner()->create();
    CleanerProfile::factory()->for($user)->create([
        'agreement_photo_path' => null,
        'agreement_signed' => false,
    ]);

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('agreementPhoto', UploadedFile::fake()->image('agreement.jpg'))
        ->call('uploadAgreement');

    $response->assertHasNoErrors();

    $profile = $user->cleanerProfile->fresh();

    expect($profile->agreement_photo_path)->not->toBeNull();
    expect($profile->agreement_signed)->toBeFalse();
    Storage::disk('public')->assertExists($profile->agreement_photo_path);
});

test('resubmitting an agreement resets an approved one back to pending', function () {
    Storage::fake('public');

    $user = User::factory()->cleaner()->create();
    CleanerProfile::factory()->for($user)->create([
        'agreement_photo_path' => 'agreements/old.jpg',
        'agreement_signed' => true,
    ]);

    $this->actingAs($user);

    Livewire::test('pages::settings.profile')
        ->set('agreementPhoto', UploadedFile::fake()->image('agreement.jpg'))
        ->call('uploadAgreement');

    $profile = $user->cleanerProfile->fresh();

    expect($profile->agreement_signed)->toBeFalse();
    Storage::disk('public')->assertMissing('agreements/old.jpg');
});

test('customer cannot submit an agreement photo', function () {
    $user = User::factory()->customer()->create();

    $this->actingAs($user);

    Livewire::test('pages::settings.profile')
        ->set('agreementPhoto', UploadedFile::fake()->image('agreement.jpg'))
        ->call('uploadAgreement')
        ->assertForbidden();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect(User::find($user->id))->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});

test('deleting an account soft-deletes it rather than removing the row', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'password')
        ->call('deleteUser');

    $trashed = User::withTrashed()->find($user->id);

    expect($trashed)->not->toBeNull();
    expect($trashed->trashed())->toBeTrue();
});

test('a soft-deleted cleaner keeps their job history intact', function () {
    $cleaner = User::factory()->cleaner()->create();
    CleanerProfile::factory()->for($cleaner)->create();
    $job = CleaningJob::factory()->completed()->create(['cleaner_id' => $cleaner->id]);

    $this->actingAs($cleaner);

    Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'password')
        ->call('deleteUser');

    expect($job->fresh()->cleaner_id)->toBe($cleaner->id);
    expect(CleanerProfile::where('user_id', $cleaner->id)->exists())->toBeTrue();
});

test('a soft-deleted customer keeps their job request history intact', function () {
    $customer = User::factory()->customer()->create();
    $job = CleaningJob::factory()->create(['customer_id' => $customer->id]);

    $this->actingAs($customer);

    Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'password')
        ->call('deleteUser');

    expect(CleaningJob::find($job->id))->not->toBeNull();
});

test('a soft-deleted user cannot log back in', function () {
    $user = User::factory()->create(['email' => 'gone@example.com']);

    $this->actingAs($user);

    Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'password')
        ->call('deleteUser');

    $this->post('/login', [
        'email' => 'gone@example.com',
        'password' => 'password',
    ]);

    expect(auth()->check())->toBeFalse();
});

test('a soft-deleted account email can be reused for a new registration', function () {
    $user = User::factory()->create(['email' => 'reuse@example.com']);
    $user->delete();

    $response = $this->post('/register', [
        'role' => 'customer',
        'name' => 'New Person',
        'email' => 'reuse@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionDoesntHaveErrors('email');
    expect(User::where('email', 'reuse@example.com')->count())->toBe(1);
});
