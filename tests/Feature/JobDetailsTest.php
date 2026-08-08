<?php

use App\Enums\Role;
use App\Models\CleaningJob;
use App\Models\User;

test('a customer cannot view the admin job details page', function () {
    $user = User::factory()->create(['role' => Role::Customer]);
    $job = CleaningJob::factory()->create(['customer_id' => $user->id]);

    $this->actingAs($user);

    $this->get(route('admin.jobs.show', $job))->assertForbidden();
});

test('admin sees full job detail, photos, and map on the job details page', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create(['name' => 'Jane Homeowner']);
    $customer->customerProfile()->create(['phone' => '267-555-0199']);

    $job = CleaningJob::factory()->create([
        'customer_id' => $customer->id,
        'address' => '7135 Rising Sun Ave, Philadelphia, PA 19111',
        'notes' => 'Ring the doorbell twice',
    ]);
    $job->photos()->create(['path' => 'jobs/before.jpg']);

    $this->actingAs($admin);

    $response = $this->get(route('admin.jobs.show', $job));

    $response->assertOk();
    $response->assertSee('7135 Rising Sun Ave, Philadelphia, PA 19111');
    $response->assertSee('Jane Homeowner');
    $response->assertSee('267-555-0199');
    $response->assertSee('Ring the doorbell twice');
    $response->assertSee('jobs/before.jpg', escape: false);
    $response->assertSee('output=embed', escape: false);
});
