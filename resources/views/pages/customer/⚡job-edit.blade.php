<?php

use App\Concerns\CleaningJobFormFields;
use App\Enums\JobStatus;
use App\Models\CleaningJob;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Edit Cleaning Request')] class extends Component
{
    use CleaningJobFormFields, WithFileUploads;

    public CleaningJob $cleaningJob;

    public function mount(int $job): void
    {
        // Scoped to the authenticated customer's own jobs — a mismatched id
        // 404s instead of leaking whether it belongs to someone else, same
        // pattern as CleanerDashboard::markComplete(). Loaded into
        // $cleaningJob (not $job) so it doesn't collide with this mount
        // parameter, which Livewire would otherwise try to hydrate directly.
        $this->cleaningJob = Auth::user()->jobsAsCustomer()->findOrFail($job);

        if ($this->cleaningJob->status !== JobStatus::Requested) {
            Flux::toast(
                variant: 'warning',
                text: __('This job has already been assigned and can no longer be edited.'),
            );

            $this->redirectRoute('customer.upcoming-jobs', navigate: true);

            return;
        }

        $this->address = $this->cleaningJob->address;
        $this->requested_at = $this->cleaningJob->requested_at->format('Y-m-d\TH:i');
        $this->property_type = $this->cleaningJob->property_type?->value ?? '';
        $this->service_type = $this->cleaningJob->service_type?->value ?? '';
        $this->frequency = $this->cleaningJob->frequency?->value ?? '';
        $this->cleaning_type = $this->cleaningJob->cleaning_type?->value ?? '';
        $this->property_size = $this->cleaningJob->property_size ?? '';
        $this->has_pets = $this->cleaningJob->has_pets;
        $this->pet_types = $this->cleaningJob->pet_types ?? [];
        $this->pet_type_other = $this->cleaningJob->pet_type_other ?? '';
        $this->pet_count = $this->cleaningJob->pet_count;
        $this->laundry_addon = $this->cleaningJob->laundry_addon;
        $this->notes = $this->cleaningJob->notes ?? '';
    }

    public function submit(): void
    {
        // Re-check in case James assigned it in the moments the form was open.
        if ($this->cleaningJob->fresh()->status !== JobStatus::Requested) {
            Flux::toast(
                variant: 'warning',
                text: __('This job was just assigned and can no longer be edited.'),
            );

            $this->redirectRoute('customer.upcoming-jobs', navigate: true);

            return;
        }

        $validated = $this->validate();

        $this->cleaningJob->update($this->cleaningJobAttributes($validated));

        $this->attachUploadedPhotos($this->cleaningJob);

        Flux::toast(variant: 'success', text: __('Job request updated.'));

        $this->redirectRoute('customer.upcoming-jobs', navigate: true);
    }
}
?>

<div class="flex h-full w-full flex-1 flex-col gap-8">
    <div class="flex flex-col gap-4">
        <flux:link :href="route('customer.upcoming-jobs')" wire:navigate class="inline-flex w-fit items-center gap-1 text-sm">
            &larr; {{ __('Back to upcoming jobs') }}
        </flux:link>

        <span class="inline-flex w-fit items-center rounded-full bg-secondary px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-primary">
            {{ __('Customer') }}
        </span>

        <flux:heading size="xl" class="text-primary">{{ __('Edit cleaning request') }}</flux:heading>

        <flux:subheading>
            {{ __('You can update these details until James assigns a cleaner.') }}
        </flux:subheading>
    </div>

    <div class="rounded-2xl border border-secondary bg-surface p-6">
        @include('partials.customer-job-form-fields', ['submitLabel' => __('Save changes')])
    </div>
</div>
