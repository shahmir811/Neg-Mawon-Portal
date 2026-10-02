<?php

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('a customer cannot access the backups screen', function () {
    $user = User::factory()->customer()->create();
    $this->actingAs($user);

    $this->get(route('admin.backups'))->assertForbidden();
});

test('a cleaner cannot access the backups screen', function () {
    $user = User::factory()->cleaner()->create();
    $this->actingAs($user);

    $this->get(route('admin.backups'))->assertForbidden();
});

test('admin sees an empty state when no backups exist', function () {
    Storage::fake('s3');

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $this->get(route('admin.backups'))
        ->assertOk()
        ->assertSee('No backups yet');
});

test('admin sees existing backups listed newest first', function () {
    Storage::fake('s3');

    $folder = config('backup.backup.name');
    Storage::disk('s3')->put("{$folder}/2026-01-01-00-00-00.zip", 'old backup');
    Storage::disk('s3')->put("{$folder}/2026-02-01-00-00-00.zip", 'newer backup');

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    Livewire::test('pages::admin.backups')
        ->assertSeeInOrder(['2026-02-01-00-00-00.zip', '2026-01-01-00-00-00.zip']);
});

test('admin can delete a backup', function () {
    Storage::fake('s3');

    $path = config('backup.backup.name').'/2026-01-01-00-00-00.zip';
    Storage::disk('s3')->put($path, 'old backup');

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    Livewire::test('pages::admin.backups')
        ->call('confirmDelete', $path)
        ->assertSet('showDeleteModal', true)
        ->call('deleteBackup')
        ->assertSet('showDeleteModal', false)
        ->assertDontSee('2026-01-01-00-00-00.zip');

    Storage::disk('s3')->assertMissing($path);
});

test('admin can trigger a manual database backup that uploads to s3', function () {
    Storage::fake('s3');

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    Livewire::test('pages::admin.backups')
        ->call('runBackup');

    $files = Storage::disk('s3')->allFiles(config('backup.backup.name'));

    expect($files)->toHaveCount(1)
        ->and($files[0])->toContain('ngm-cleaning-db-backup-')
        ->and($files[0])->toEndWith('.zip');
});
