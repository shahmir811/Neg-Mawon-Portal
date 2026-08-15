<?php

use App\Concerns\CleaningJobFormFields;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Your Dashboard')] class extends Component
{
    use CleaningJobFormFields, WithFileUploads;

    public function submit(): void
    {
        $validated = $this->validate();

        $job = Auth::user()->jobsAsCustomer()->create($this->cleaningJobAttributes($validated));

        $this->attachUploadedPhotos($job);

        $this->reset([
            'address', 'requested_at', 'property_type', 'service_type', 'frequency', 'cleaning_type',
            'property_size', 'bedroom_count', 'bathroom_count', 'floor_type', 'has_pets', 'pet_types',
            'pet_type_other', 'pet_count', 'laundry_addon', 'notes', 'photos',
        ]);

        Flux::toast(
            variant: 'success',
            text: __('Job request submitted — James will review it and be in touch soon.'),
        );

        $this->redirectRoute('customer.upcoming-jobs', navigate: true);
    }
}
?>

<div class="flex h-full w-full flex-1 flex-col gap-8">
    <div class="flex flex-col gap-4">
        <span class="inline-flex w-fit items-center rounded-full bg-secondary px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-primary">
            {{ __('Customer') }}
        </span>

        <flux:heading size="xl" class="text-primary">
            {{ __('Welcome back, :name.', ['name' => auth()->user()->name]) }}
        </flux:heading>

        <flux:subheading>
            {{ __("Tell us about your home below and James will assign you a cleaner. You'll find your requests under Upcoming Jobs.") }}
        </flux:subheading>
    </div>

    <div class="rounded-2xl border border-secondary bg-surface p-6">
        <flux:heading size="lg" class="mb-4 text-primary">{{ __('Request a cleaning') }}</flux:heading>

        @include('partials.customer-job-form-fields')
    </div>
</div>
