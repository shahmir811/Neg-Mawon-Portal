<?php

use App\Enums\JobStatus;
use App\Models\CleaningJob;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Upcoming Jobs')] class extends Component
{
    /**
     * Not-yet-completed jobs (requested or assigned), most recently
     * requested first. Only fields the privacy rule allows a customer to see
     * (AGENTS.md Section 5) — job details plus the assigned cleaner's photo,
     * never the cleaner's name, phone, email, or ID.
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function upcomingJobs(): array
    {
        return Auth::user()->jobsAsCustomer()
            ->with('photos')
            ->whereIn('status', [JobStatus::Requested, JobStatus::Assigned])
            ->latest('created_at')
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

        <flux:heading size="xl" class="text-primary">{{ __('Upcoming jobs') }}</flux:heading>

        <flux:subheading>
            {{ __('Cleanings you have requested that are still waiting on James or your assigned cleaner.') }}
        </flux:subheading>
    </div>

    <div class="flex flex-col gap-4">
        @forelse ($this->upcomingJobs as $job)
            <x-customer-job-card :job="$job" wire:key="upcoming-{{ $job['id'] }}" />
        @empty
            <div class="rounded-2xl border border-dashed border-secondary bg-surface p-6 text-center">
                <flux:text>
                    {{ __("You haven't requested a cleaning yet.") }}
                    <flux:link :href="route('customer.dashboard')" wire:navigate>{{ __('Request one now') }}</flux:link>
                </flux:text>
            </div>
        @endforelse
    </div>
</div>
