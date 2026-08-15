<?php

use App\Enums\CleaningType;
use App\Enums\FloorType;
use App\Enums\JobFrequency;
use App\Enums\JobStatus;
use App\Enums\PetType;
use App\Enums\PropertyType;
use App\Enums\ServiceType;
use App\Models\CleaningJob;
use App\Models\User;
use App\Notifications\CleanerAssigned;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('a customer can submit a job request', function () {
    $customer = User::factory()->customer()->create();
    $this->actingAs($customer);

    Livewire::test('pages::customer.dashboard')
        ->set('address', '123 Test St, Philadelphia, PA 19111')
        ->set('requested_at', now()->addDay()->format('Y-m-d\TH:i'))
        ->set('property_type', PropertyType::Residential->value)
        ->set('service_type', ServiceType::HouseCleaning->value)
        ->set('frequency', JobFrequency::OneTime->value)
        ->set('cleaning_type', CleaningType::Deep->value)
        ->set('property_size', '1,000-3,000 sq ft')
        ->set('bedroom_count', 3)
        ->set('bathroom_count', 2)
        ->set('floor_type', FloorType::Mixed->value)
        ->set('has_pets', true)
        ->set('pet_types', [PetType::Dog->value, PetType::Cat->value])
        ->set('pet_count', 2)
        ->set('laundry_addon', true)
        ->set('notes', 'Ring the doorbell twice')
        ->call('submit');

    $job = CleaningJob::where('customer_id', $customer->id)->sole();

    expect($job->status)->toBe(JobStatus::Requested)
        ->and($job->address)->toBe('123 Test St, Philadelphia, PA 19111')
        ->and($job->notes)->toBe('Ring the doorbell twice')
        ->and($job->property_type)->toBe(PropertyType::Residential)
        ->and($job->service_type)->toBe(ServiceType::HouseCleaning)
        ->and($job->frequency)->toBe(JobFrequency::OneTime)
        ->and($job->cleaning_type)->toBe(CleaningType::Deep)
        ->and($job->bedroom_count)->toBe(3)
        ->and($job->bathroom_count)->toBe(2)
        ->and($job->floor_type)->toBe(FloorType::Mixed)
        ->and($job->has_pets)->toBeTrue()
        ->and($job->pet_types)->toBe([PetType::Dog->value, PetType::Cat->value])
        ->and($job->pet_count)->toBe(2)
        ->and($job->laundry_addon)->toBeTrue()
        ->and((float) $job->estimated_price)->toBeGreaterThan(0);
});

test('a customer can submit a job request for a commercial property', function () {
    $customer = User::factory()->customer()->create();
    $this->actingAs($customer);

    Livewire::test('pages::customer.dashboard')
        ->set('address', '789 Business Blvd, Philadelphia, PA 19111')
        ->set('requested_at', now()->addDay()->format('Y-m-d\TH:i'))
        ->set('property_type', PropertyType::Commercial->value)
        ->set('service_type', ServiceType::JanitorialServices->value)
        ->set('frequency', JobFrequency::Weekly->value)
        ->set('cleaning_type', CleaningType::Deep->value)
        ->set('property_size', '3,000-5,000 sq ft')
        ->call('submit');

    $job = CleaningJob::where('customer_id', $customer->id)->sole();

    expect($job->property_type)->toBe(PropertyType::Commercial);
});

test('selecting other as a pet type requires specifying what it is', function () {
    $customer = User::factory()->customer()->create();
    $this->actingAs($customer);

    Livewire::test('pages::customer.dashboard')
        ->set('address', '123 Test St, Philadelphia, PA 19111')
        ->set('requested_at', now()->addDay()->format('Y-m-d\TH:i'))
        ->set('property_type', PropertyType::Residential->value)
        ->set('service_type', ServiceType::HouseCleaning->value)
        ->set('frequency', JobFrequency::OneTime->value)
        ->set('cleaning_type', CleaningType::Deep->value)
        ->set('property_size', '1,000-3,000 sq ft')
        ->set('has_pets', true)
        ->set('pet_types', [PetType::Other->value])
        ->set('pet_count', 1)
        ->call('submit')
        ->assertHasErrors('pet_type_other');

    expect(CleaningJob::where('customer_id', $customer->id)->count())->toBe(0);
});

test('the specified other pet description is saved and shown to admin and cleaner', function () {
    $customer = User::factory()->customer()->create();
    $this->actingAs($customer);

    Livewire::test('pages::customer.dashboard')
        ->set('address', '123 Test St, Philadelphia, PA 19111')
        ->set('requested_at', now()->addDay()->format('Y-m-d\TH:i'))
        ->set('property_type', PropertyType::Residential->value)
        ->set('service_type', ServiceType::HouseCleaning->value)
        ->set('frequency', JobFrequency::OneTime->value)
        ->set('cleaning_type', CleaningType::Deep->value)
        ->set('property_size', '1,000-3,000 sq ft')
        ->set('bedroom_count', 2)
        ->set('bathroom_count', 1)
        ->set('has_pets', true)
        ->set('pet_types', [PetType::Other->value])
        ->set('pet_type_other', 'Pet rabbit')
        ->set('pet_count', 1)
        ->call('submit')
        ->assertHasNoErrors();

    $job = CleaningJob::where('customer_id', $customer->id)->sole();

    expect($job->pet_type_other)->toBe('Pet rabbit');

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);
    $this->get(route('admin.jobs.show', $job->id))->assertSee('Other (Pet rabbit)');

    $cleaner = User::factory()->cleaner()->create();
    $job->update(['cleaner_id' => $cleaner->id, 'status' => JobStatus::Assigned]);
    $this->actingAs($cleaner);
    $this->get(route('cleaner.dashboard'))->assertSee('Pet rabbit');
});

test('a customer can attach property photos to a job request', function () {
    Storage::fake('public');

    $customer = User::factory()->customer()->create();
    $this->actingAs($customer);

    Livewire::test('pages::customer.dashboard')
        ->set('address', '123 Test St, Philadelphia, PA 19111')
        ->set('requested_at', now()->addDay()->format('Y-m-d\TH:i'))
        ->set('property_type', PropertyType::Residential->value)
        ->set('service_type', ServiceType::HouseCleaning->value)
        ->set('frequency', JobFrequency::OneTime->value)
        ->set('cleaning_type', CleaningType::Deep->value)
        ->set('property_size', '1,000-3,000 sq ft')
        ->set('bedroom_count', 3)
        ->set('bathroom_count', 2)
        ->set('photos', [UploadedFile::fake()->image('kitchen.jpg'), UploadedFile::fake()->image('yard.jpg')])
        ->call('submit');

    $job = CleaningJob::where('customer_id', $customer->id)->sole();

    expect($job->photos)->toHaveCount(2);

    Storage::disk('public')->assertExists($job->photos->first()->path);
});

test('a first-time customer is only offered deep cleaning', function () {
    $customer = User::factory()->customer()->create();
    $this->actingAs($customer);

    $component = Livewire::test('pages::customer.dashboard');

    expect($component->get('allowedCleaningTypes'))->toBe([CleaningType::Deep]);

    $component
        ->set('address', '123 Test St, Philadelphia, PA 19111')
        ->set('requested_at', now()->addDay()->format('Y-m-d\TH:i'))
        ->set('property_type', PropertyType::Residential->value)
        ->set('service_type', ServiceType::HouseCleaning->value)
        ->set('frequency', JobFrequency::OneTime->value)
        ->set('cleaning_type', CleaningType::Soft->value)
        ->set('property_size', '1,000-3,000 sq ft')
        ->call('submit')
        ->assertHasErrors('cleaning_type');

    expect(CleaningJob::where('customer_id', $customer->id)->count())->toBe(0);
});

test('a customer whose last completed job was over 30 days ago is only offered deep cleaning', function () {
    $customer = User::factory()->customer()->create();

    CleaningJob::factory()->completed()->create([
        'customer_id' => $customer->id,
        'requested_at' => now()->subDays(45),
    ]);

    $this->actingAs($customer);

    $component = Livewire::test('pages::customer.dashboard');

    expect($component->get('allowedCleaningTypes'))->toBe([CleaningType::Deep]);
});

test('a repeat customer serviced within the last 30 days can select soft cleaning', function () {
    $customer = User::factory()->customer()->create();

    CleaningJob::factory()->completed()->create([
        'customer_id' => $customer->id,
        'requested_at' => now()->subDays(10),
    ]);

    $this->actingAs($customer);

    $component = Livewire::test('pages::customer.dashboard');

    expect($component->get('allowedCleaningTypes'))->toBe(CleaningType::cases());

    $component
        ->set('address', '456 Repeat Ave, Philadelphia, PA 19111')
        ->set('requested_at', now()->addDay()->format('Y-m-d\TH:i'))
        ->set('property_type', PropertyType::Residential->value)
        ->set('service_type', ServiceType::HouseCleaning->value)
        ->set('frequency', JobFrequency::Monthly->value)
        ->set('cleaning_type', CleaningType::Soft->value)
        ->set('property_size', '1,000-3,000 sq ft')
        ->set('bedroom_count', 2)
        ->set('bathroom_count', 1)
        ->call('submit')
        ->assertHasNoErrors();

    $job = CleaningJob::where('customer_id', $customer->id)->where('status', JobStatus::Requested)->sole();

    expect($job->cleaning_type)->toBe(CleaningType::Soft);
});

test('a job request includes a google maps link derived from the address', function () {
    $job = CleaningJob::factory()->create(['address' => '7135 Rising Sun Ave, Philadelphia, PA 19111']);

    expect($job->googleMapsUrl())
        ->toContain('google.com/maps')
        ->toContain(urlencode('7135 Rising Sun Ave, Philadelphia, PA 19111'));
});

test('a customer sees not-yet-completed jobs on the upcoming jobs page, not completed ones', function () {
    $customer = User::factory()->customer()->create();

    $upcoming = CleaningJob::factory()->create([
        'customer_id' => $customer->id,
        'address' => '10 Upcoming St, Philadelphia, PA 19111',
        'status' => JobStatus::Requested,
    ]);

    $completed = CleaningJob::factory()->create([
        'customer_id' => $customer->id,
        'address' => '20 History Ave, Philadelphia, PA 19111',
        'status' => JobStatus::Completed,
    ]);

    $this->actingAs($customer);

    $component = Livewire::test('pages::customer.upcoming-jobs');

    expect(collect($component->get('upcomingJobs'))->pluck('id'))
        ->toContain($upcoming->id)
        ->not->toContain($completed->id);

    $component->assertSee('10 Upcoming St, Philadelphia, PA 19111')
        ->assertDontSee('20 History Ave, Philadelphia, PA 19111');
});

test('a customer sees completed jobs on the job history page, not upcoming ones', function () {
    $customer = User::factory()->customer()->create();

    $upcoming = CleaningJob::factory()->create([
        'customer_id' => $customer->id,
        'address' => '10 Upcoming St, Philadelphia, PA 19111',
        'status' => JobStatus::Assigned,
    ]);

    $completed = CleaningJob::factory()->create([
        'customer_id' => $customer->id,
        'address' => '20 History Ave, Philadelphia, PA 19111',
        'status' => JobStatus::Completed,
    ]);

    $this->actingAs($customer);

    $component = Livewire::test('pages::customer.job-history');

    expect(collect($component->get('completedJobs'))->pluck('id'))
        ->toContain($completed->id)
        ->not->toContain($upcoming->id);

    $component->assertSee('20 History Ave, Philadelphia, PA 19111')
        ->assertDontSee('10 Upcoming St, Philadelphia, PA 19111');
});

test('the address map preview only appears once an address has been entered', function () {
    $customer = User::factory()->customer()->create();
    $this->actingAs($customer);

    Livewire::test('pages::customer.dashboard')
        ->assertDontSee('output=embed', escape: false)
        ->set('address', '123 Test St, Philadelphia, PA 19111')
        ->assertSee('output=embed', escape: false)
        ->assertSee(urlencode('123 Test St, Philadelphia, PA 19111'), escape: false);
});

test('a customer only ever sees the assigned cleaners photo, never their name, phone, or email', function () {
    $customer = User::factory()->customer()->create();
    $cleaner = User::factory()->cleaner()->create(['name' => 'Marie Joseph', 'email' => 'marie@example.com']);
    $cleaner->cleanerProfile()->create([
        'phone' => '267-555-0100',
        'photo_path' => 'cleaners/marie.jpg',
        'agreement_signed' => true,
    ]);

    CleaningJob::factory()->create([
        'customer_id' => $customer->id,
        'cleaner_id' => $cleaner->id,
        'status' => JobStatus::Assigned,
    ]);

    $this->actingAs($customer);

    $response = $this->get(route('customer.upcoming-jobs'));

    $response->assertOk();
    $response->assertDontSee('Marie Joseph');
    $response->assertDontSee('marie@example.com');
    $response->assertDontSee('267-555-0100');
    $response->assertSee('cleaners/marie.jpg', escape: false);
});

test('admin can assign a cleaner to a requested job and the customer is notified', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create();
    $cleaner = User::factory()->cleaner()->create();
    $cleaner->cleanerProfile()->create(['agreement_signed' => true]);

    $job = CleaningJob::factory()->create([
        'customer_id' => $customer->id,
        'status' => JobStatus::Requested,
    ]);

    $this->actingAs($admin);

    Livewire::test('pages::admin.dashboard')
        ->set("pendingCleaner.{$job->id}", $cleaner->id)
        ->call('assign', $job->id);

    $job->refresh();

    expect($job->status)->toBe(JobStatus::Assigned)
        ->and($job->cleaner_id)->toBe($cleaner->id);

    Notification::assertSentTo($customer, CleanerAssigned::class);
});

test('a cleaner without an approved agreement is not offered in the assign dropdown', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create();

    $unapproved = User::factory()->cleaner()->create(['name' => 'Angela Saint-Louis']);
    $unapproved->cleanerProfile()->create(['agreement_signed' => false]);

    $approved = User::factory()->cleaner()->create(['name' => 'Devon Pierre']);
    $approved->cleanerProfile()->create(['agreement_photo_path' => 'agreements/devon.jpg', 'agreement_signed' => true]);

    CleaningJob::factory()->create([
        'customer_id' => $customer->id,
        'status' => JobStatus::Requested,
    ]);

    $this->actingAs($admin);

    $component = Livewire::test('pages::admin.dashboard');

    $ids = collect($component->get('cleaners'))->pluck('id');

    expect($ids)->not->toContain($unapproved->id)
        ->and($ids)->toContain($approved->id);
});

test('admin cannot assign a job to a cleaner whose agreement is not approved', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create();

    $cleaner = User::factory()->cleaner()->create();
    $cleaner->cleanerProfile()->create(['agreement_signed' => false]);

    $job = CleaningJob::factory()->create([
        'customer_id' => $customer->id,
        'status' => JobStatus::Requested,
    ]);

    $this->actingAs($admin);

    Livewire::test('pages::admin.dashboard')
        ->set("pendingCleaner.{$job->id}", $cleaner->id)
        ->call('assign', $job->id)
        ->assertForbidden();

    $job->refresh();

    expect($job->status)->toBe(JobStatus::Requested)
        ->and($job->cleaner_id)->toBeNull();

    Notification::assertNothingSent();
});

test('a soft-deleted cleaner is not offered in the assign dropdown and cannot be assigned', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create();

    $cleaner = User::factory()->cleaner()->create();
    $cleaner->cleanerProfile()->create(['agreement_photo_path' => 'agreements/deleted.jpg', 'agreement_signed' => true]);
    $cleaner->delete();

    $job = CleaningJob::factory()->create([
        'customer_id' => $customer->id,
        'status' => JobStatus::Requested,
    ]);

    $this->actingAs($admin);

    $component = Livewire::test('pages::admin.dashboard');

    expect(collect($component->get('cleaners'))->pluck('id'))->not->toContain($cleaner->id);

    $component->set("pendingCleaner.{$job->id}", $cleaner->id);

    expect(fn () => $component->call('assign', $job->id))->toThrow(ModelNotFoundException::class);

    $job->refresh();

    expect($job->status)->toBe(JobStatus::Requested)
        ->and($job->cleaner_id)->toBeNull();

    Notification::assertNothingSent();
});

test('admin job detail page shows pets and laundry add-on so James knows before assigning', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create();

    $job = CleaningJob::factory()->withPets()->create([
        'customer_id' => $customer->id,
        'laundry_addon' => true,
        'status' => JobStatus::Requested,
    ]);

    $this->actingAs($admin);

    $response = $this->get(route('admin.jobs.show', $job->id));

    $response->assertOk();
    $response->assertSee('Laundry add-on');
    $response->assertSee('Dog');
});

test('admin dashboard shows full job and customer detail', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create(['name' => 'Jane Homeowner']);
    $customer->customerProfile()->create(['phone' => '267-555-0199']);

    CleaningJob::factory()->create([
        'customer_id' => $customer->id,
        'status' => JobStatus::Requested,
    ]);

    $this->actingAs($admin);

    $response = $this->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Jane Homeowner');
    $response->assertSee('267-555-0199');
});

test('a cleaner can mark their own assigned job complete', function () {
    $cleaner = User::factory()->cleaner()->create();
    $customer = User::factory()->customer()->create();

    $job = CleaningJob::factory()->create([
        'customer_id' => $customer->id,
        'cleaner_id' => $cleaner->id,
        'status' => JobStatus::Assigned,
    ]);

    $this->actingAs($cleaner);

    Livewire::test('pages::cleaner.dashboard')->call('markComplete', $job->id);

    expect($job->fresh()->status)->toBe(JobStatus::Completed);
});

test('a cleaner cannot mark another cleaners job complete', function () {
    $cleaner = User::factory()->cleaner()->create();
    $otherCleaner = User::factory()->cleaner()->create();
    $customer = User::factory()->customer()->create();

    $job = CleaningJob::factory()->create([
        'customer_id' => $customer->id,
        'cleaner_id' => $cleaner->id,
        'status' => JobStatus::Assigned,
    ]);

    $this->actingAs($otherCleaner);

    expect(fn () => Livewire::test('pages::cleaner.dashboard')->call('markComplete', $job->id))
        ->toThrow(ModelNotFoundException::class);

    expect($job->fresh()->status)->toBe(JobStatus::Assigned);
});

test('a cleaner sees pets and laundry add-on details on their assigned job', function () {
    $cleaner = User::factory()->cleaner()->create();
    $customer = User::factory()->customer()->create();

    CleaningJob::factory()->withPets()->create([
        'customer_id' => $customer->id,
        'cleaner_id' => $cleaner->id,
        'laundry_addon' => true,
        'status' => JobStatus::Assigned,
    ]);

    $this->actingAs($cleaner);

    $response = $this->get(route('cleaner.dashboard'));

    $response->assertOk();
    $response->assertSee('Laundry add-on');
    $response->assertSee('1 pet(s)');
});

test('a cleaner never sees the customers name, phone, or email', function () {
    $cleaner = User::factory()->cleaner()->create();
    $customer = User::factory()->customer()->create(['name' => 'Jane Homeowner', 'email' => 'jane@example.com']);
    $customer->customerProfile()->create(['phone' => '267-555-0199']);

    CleaningJob::factory()->create([
        'customer_id' => $customer->id,
        'cleaner_id' => $cleaner->id,
        'status' => JobStatus::Assigned,
    ]);

    $this->actingAs($cleaner);

    $response = $this->get(route('cleaner.dashboard'));

    $response->assertOk();
    $response->assertDontSee('Jane Homeowner');
    $response->assertDontSee('jane@example.com');
    $response->assertDontSee('267-555-0199');
});
