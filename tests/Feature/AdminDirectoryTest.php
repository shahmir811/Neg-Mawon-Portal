<?php

use App\Enums\JobStatus;
use App\Models\CleaningJob;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('a customer cannot access the cleaners or customers directory', function () {
    $user = User::factory()->customer()->create();
    $this->actingAs($user);

    $this->get(route('admin.cleaners'))->assertForbidden();
    $this->get(route('admin.customers'))->assertForbidden();
});

test('a cleaner cannot access the cleaners or customers directory', function () {
    $user = User::factory()->cleaner()->create();
    $this->actingAs($user);

    $this->get(route('admin.cleaners'))->assertForbidden();
    $this->get(route('admin.customers'))->assertForbidden();
});

test('admin sees agreement and subscription status for every cleaner', function () {
    $admin = User::factory()->admin()->create();

    $cleaner = User::factory()->cleaner()->create(['name' => 'Marie Joseph']);
    $cleaner->cleanerProfile()->create([
        'phone' => '267-555-0100',
        'agreement_photo_path' => 'agreements/marie.jpg',
        'agreement_signed' => true,
    ]);

    $this->actingAs($admin);

    $response = $this->get(route('admin.cleaners'));

    $response->assertOk();
    $response->assertSee('Marie Joseph');
    $response->assertSee('267-555-0100');
    $response->assertSee('Approved');
});

test('admin sees every customer and can view their job history', function () {
    $admin = User::factory()->admin()->create();

    $customer = User::factory()->customer()->create(['name' => 'Jane Homeowner']);
    $customer->customerProfile()->create(['phone' => '267-555-0199']);

    $job = CleaningJob::factory()->create([
        'customer_id' => $customer->id,
        'address' => '42 Elm St, Philadelphia, PA 19111',
        'status' => JobStatus::Requested,
    ]);

    $this->actingAs($admin);

    $this->get(route('admin.customers'))
        ->assertOk()
        ->assertSee('Jane Homeowner')
        ->assertSee('267-555-0199');

    Livewire::test('pages::admin.customers')
        ->call('viewJobs', $customer->id)
        ->assertSee('42 Elm St, Philadelphia, PA 19111');
});

test('admin can approve a cleaner-submitted agreement without re-uploading it', function () {
    $admin = User::factory()->admin()->create();

    $cleaner = User::factory()->cleaner()->create(['name' => 'Marie Joseph']);
    $cleaner->cleanerProfile()->create([
        'agreement_photo_path' => 'agreements/submitted-by-cleaner.jpg',
        'agreement_signed' => false,
    ]);

    $this->actingAs($admin);

    Livewire::test('pages::admin.cleaners')->call('approveAgreement', $cleaner->id);

    expect($cleaner->cleanerProfile->fresh()->agreement_signed)->toBeTrue();
});

test('admin cannot approve an agreement that was never submitted', function () {
    $admin = User::factory()->admin()->create();

    $cleaner = User::factory()->cleaner()->create();
    $cleaner->cleanerProfile()->create(['agreement_photo_path' => null, 'agreement_signed' => false]);

    $this->actingAs($admin);

    Livewire::test('pages::admin.cleaners')
        ->call('approveAgreement', $cleaner->id)
        ->assertForbidden();
});

test('admin can upload a cleaners signed agreement photo, marking it signed', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();

    $cleaner = User::factory()->cleaner()->create(['name' => 'Marie Joseph']);
    $cleaner->cleanerProfile()->create(['agreement_signed' => false]);

    $this->actingAs($admin);

    Livewire::test('pages::admin.cleaners')
        ->call('openAgreementUpload', $cleaner->id)
        ->set('agreementPhoto', UploadedFile::fake()->image('agreement.jpg'))
        ->call('uploadAgreement');

    $cleaner->cleanerProfile->refresh();

    expect($cleaner->cleanerProfile->agreement_signed)->toBeTrue();

    Storage::disk('public')->assertExists($cleaner->cleanerProfile->agreement_photo_path);
});
