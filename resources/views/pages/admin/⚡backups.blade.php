<?php

use Flux\Flux;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Number;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Backup\BackupDestination\BackupDestination;

new #[Title('Backups')] class extends Component
{
    public bool $isRunningBackup = false;

    public bool $showDeleteModal = false;

    public ?string $deletingBackupPath = null;

    /**
     * Database-only backup, uploaded to the "s3" disk — ngmcleaning-dev locally,
     * ngmcleaning-prod in production (see config/filesystems.php, config/backup.php).
     * Mirrors the scheduled `backup:run --only-db` in routes/console.php.
     */
    public function runBackup(): void
    {
        $this->isRunningBackup = true;

        $exitCode = Artisan::call('backup:run', ['--only-db' => true]);

        $this->isRunningBackup = false;

        unset($this->backups);

        if ($exitCode === 0) {
            Flux::toast(variant: 'success', text: __('Backup completed and uploaded to S3.'));
        } else {
            Flux::toast(variant: 'danger', text: __('Backup failed. Check the application log for details.'));
        }
    }

    public function confirmDelete(string $path): void
    {
        $this->deletingBackupPath = $path;
        $this->showDeleteModal = true;
    }

    public function deleteBackup(): void
    {
        if (! $this->deletingBackupPath) {
            return;
        }

        $backup = BackupDestination::create('s3', config('backup.backup.name'))
            ->backups()
            ->first(fn ($backup) => $backup->path() === $this->deletingBackupPath);

        $backup?->delete();

        $this->reset(['showDeleteModal', 'deletingBackupPath']);

        unset($this->backups);

        Flux::toast(variant: 'success', text: __('Backup deleted.'));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function backups(): array
    {
        return BackupDestination::create('s3', config('backup.backup.name'))
            ->backups()
            ->map(fn ($backup) => [
                'path' => $backup->path(),
                'filename' => basename($backup->path()),
                'date' => $backup->date(),
                'size' => $backup->sizeInBytes(),
            ])
            ->all();
    }

    #[Computed]
    public function deletingBackupFilename(): ?string
    {
        return $this->deletingBackupPath ? basename($this->deletingBackupPath) : null;
    }
}
?>

<div class="flex h-full w-full flex-1 flex-col gap-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex flex-col gap-4">
            <span class="inline-flex w-fit items-center rounded-full bg-secondary px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-primary">
                {{ __('Admin') }}
            </span>

            <flux:heading size="xl" class="text-primary">{{ __('Backups') }}</flux:heading>

            <flux:subheading>
                {{ __('Database backups, uploaded to S3 automatically every 6 hours. Backups older than 30 days are removed on the first Sunday of each month.') }}
            </flux:subheading>
        </div>

        <flux:button variant="primary" wire:click="runBackup" wire:loading.attr="disabled" wire:target="runBackup">
            <span wire:loading.remove wire:target="runBackup">{{ __('Back up now') }}</span>
            <span wire:loading wire:target="runBackup">{{ __('Backing up…') }}</span>
        </flux:button>
    </div>

    @if (count($this->backups) > 0)
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('File') }}</flux:table.column>
                <flux:table.column>{{ __('Created') }}</flux:table.column>
                <flux:table.column>{{ __('Size') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->backups as $backup)
                    <flux:table.row wire:key="backup-{{ $backup['path'] }}">
                        <flux:table.cell>{{ $backup['filename'] }}</flux:table.cell>
                        <flux:table.cell>{{ $backup['date']->format('M j, Y g:i A T') }}</flux:table.cell>
                        <flux:table.cell>{{ Number::fileSize($backup['size'], precision: 1) }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:button size="sm" variant="danger" wire:click="confirmDelete('{{ $backup['path'] }}')">
                                {{ __('Delete') }}
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @else
        <div class="rounded-2xl border border-dashed border-secondary bg-surface p-6 text-center">
            <flux:text>{{ __('No backups yet. Click "Back up now" to create the first one.') }}</flux:text>
        </div>
    @endif

    <flux:modal wire:model.self="showDeleteModal" class="w-full max-w-md">
        <div class="flex flex-col gap-4">
            <flux:heading size="lg" class="text-primary">{{ __('Delete this backup?') }}</flux:heading>

            <flux:text class="text-text/70">
                {{ __('This permanently deletes :filename from S3. This cannot be undone.', ['filename' => $this->deletingBackupFilename]) }}
            </flux:text>

            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">
                    {{ __('Cancel') }}
                </flux:button>

                <flux:button variant="danger" wire:click="deleteBackup">
                    {{ __('Delete backup') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
