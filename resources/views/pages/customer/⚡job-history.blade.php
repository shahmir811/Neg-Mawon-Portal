<?php

use App\Enums\JobStatus;
use App\Models\CleaningJob;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Job History')] class extends Component
{
    /**
     * Past completed jobs, most recent first. Only fields the privacy rule
     * allows a customer to see (AGENTS.md Section 5) — job details plus the
     * assigned cleaner's photo, never the cleaner's name, phone, email, or ID.
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function completedJobs(): array
    {
        return Auth::user()->jobsAsCustomer()
            ->with('photos')
            ->where('status', JobStatus::Completed)
            ->latest('requested_at')
            ->get()
            ->map(fn (CleaningJob $job) => $job->toCustomerArray())
            ->all();
    }
}
?>

<div class="flex h-full w-full flex-1 flex-col gap-8">
    <div class="flex flex-col gap-4">
        <span class="inline-flex w-fit items-center rounded-full bg-secondary px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-primary">
            {{ __('Customer') }}
        </span>

        <flux:heading size="xl" class="text-primary">{{ __('Job history') }}</flux:heading>

        <flux:subheading>
            {{ __('Cleanings your cleaner has already completed.') }}
        </flux:subheading>
    </div>

    <div class="flex flex-col gap-4">
        @forelse ($this->completedJobs as $job)
            <x-customer-job-card :job="$job" wire:key="completed-{{ $job['id'] }}" />
        @empty
            <div class="rounded-2xl border border-dashed border-secondary bg-surface p-6 text-center">
                <flux:text>{{ __('Completed jobs will show up here once your first cleaning is done.') }}</flux:text>
            </div>
        @endforelse
    </div>
</div>
