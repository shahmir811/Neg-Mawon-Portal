<?php

use App\Enums\JobStatus;
use App\Enums\SubscriptionStatus;
use App\Models\CleaningJob;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Cleaner Dashboard')] class extends Component
{
    public function markComplete(int $jobId): void
    {
        $job = Auth::user()->jobsAsCleaner()->findOrFail($jobId);

        abort_unless($job->status === JobStatus::Assigned, 403);

        $job->update(['status' => JobStatus::Completed]);

        Flux::toast(variant: 'success', text: __('Job marked complete. Nice work!'));
    }

    /**
     * Job details only — no customer name, phone, or email (James remains
     * the sole point of contact between cleaners and customers).
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function assignedJobs(): array
    {
        return Auth::user()->jobsAsCleaner()
            ->with('photos')
            ->where('status', JobStatus::Assigned)
            ->oldest('requested_at')
            ->get()
            ->map(fn (CleaningJob $job) => $job->toPortalArray())
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function completedJobs(): array
    {
        return Auth::user()->jobsAsCleaner()
            ->with('photos')
            ->where('status', JobStatus::Completed)
            ->latest('requested_at')
            ->limit(10)
            ->get()
            ->map(fn (CleaningJob $job) => $job->toPortalArray())
            ->all();
    }
}
?>

<div class="flex h-full w-full flex-1 flex-col gap-8">
    <div class="flex flex-col gap-4">
        <span class="inline-flex w-fit items-center rounded-full bg-secondary px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-primary">
            {{ __('Cleaner') }}
        </span>

        <flux:heading size="xl" class="text-primary">
            {{ __('Welcome back, :name.', ['name' => auth()->user()->name]) }}
        </flux:heading>

        <flux:subheading>
            {{ __('Your assigned jobs and subscription status.') }}
        </flux:subheading>
    </div>

    @php $profile = auth()->user()->cleanerProfile @endphp
    @if ($profile)
        <div class="rounded-2xl border border-secondary bg-surface p-6">
            <flux:heading size="lg" class="mb-2 text-primary">{{ __('Your subscription') }}</flux:heading>
            <div class="flex flex-wrap items-center gap-3">
                <flux:badge :color="$profile->subscription_status === SubscriptionStatus::Active ? 'green' : 'zinc'">
                    {{ $profile->subscription_status === SubscriptionStatus::Active ? __('Active') : __('Inactive') }}
                </flux:badge>

                @if ($profile->subscription_plan)
                    <flux:text>{{ ucfirst($profile->subscription_plan->value) }} {{ __('plan') }}</flux:text>
                @endif

                @if ($profile->next_renewal_at)
                    <flux:text class="text-text/70">
                        {{ __('Renews :date', ['date' => $profile->next_renewal_at->format('M j, Y')]) }}
                    </flux:text>
                @endif
            </div>
        </div>

        <div class="rounded-2xl border border-secondary bg-surface p-6">
            <flux:heading size="lg" class="mb-2 text-primary">{{ __('Exclusivity agreement') }}</flux:heading>
            <div class="flex flex-wrap items-center gap-3">
                <flux:badge
                    :color="match ($profile->agreementStatus()) {
                        \App\Enums\AgreementStatus::Approved => 'green',
                        \App\Enums\AgreementStatus::Pending => 'blue',
                        \App\Enums\AgreementStatus::NotSubmitted => 'amber',
                    }"
                >
                    {{ match ($profile->agreementStatus()) {
                        \App\Enums\AgreementStatus::Approved => __('Approved'),
                        \App\Enums\AgreementStatus::Pending => __('Pending review'),
                        \App\Enums\AgreementStatus::NotSubmitted => __('Not submitted'),
                    } }}
                </flux:badge>

                @if ($profile->agreementStatus() !== \App\Enums\AgreementStatus::Approved)
                    <flux:link :href="route('profile.edit')" wire:navigate class="text-sm">
                        {{ __('Submit in Settings →') }}
                    </flux:link>
                @endif
            </div>
        </div>
    @endif

    <div class="flex flex-col gap-4">
        <flux:heading size="lg" class="text-primary">{{ __('Assigned jobs') }}</flux:heading>

        @forelse ($this->assignedJobs as $job)
            <div
                wire:key="assigned-{{ $job['id'] }}"
                class="flex flex-col gap-4 rounded-2xl border border-secondary bg-surface p-5 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <flux:heading class="text-primary">
                        {{ $job['address'] }}
                        <flux:link :href="$job['google_maps_url']" target="_blank" class="text-sm font-normal">
                            {{ __('View on map') }}
                        </flux:link>
                    </flux:heading>
                    <flux:text>
                        {{ $job['requested_at']->format('M j, Y \a\t g:i A') }} &middot; {{ $job['property_size'] }}
                        @if ($job['cleaning_type'])
                            &middot; {{ $job['cleaning_type']->label() }}
                        @endif
                    </flux:text>
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        @if ($job['has_pets'])
                            <flux:badge size="sm" color="zinc">
                                {{ __(':count pet(s)', ['count' => $job['pet_count'] ?? '?']) }}
                                @if ($job['pet_type_other'])
                                    &middot; {{ $job['pet_type_other'] }}
                                @endif
                            </flux:badge>
                        @endif
                        @if ($job['laundry_addon'])
                            <flux:badge size="sm" color="amber">{{ __('Laundry add-on') }}</flux:badge>
                        @endif
                    </div>
                    @if ($job['notes'])
                        <flux:text class="text-text/70">{{ $job['notes'] }}</flux:text>
                    @endif
                    @if (count($job['photo_urls']) > 0)
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($job['photo_urls'] as $photoUrl)
                                <img src="{{ $photoUrl }}" class="h-14 w-14 rounded-lg object-cover" />
                            @endforeach
                        </div>
                    @endif
                </div>

                <flux:button wire:click="markComplete({{ $job['id'] }})" variant="primary">
                    {{ __('Mark complete') }}
                </flux:button>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-secondary bg-surface p-6 text-center">
                <flux:text>{{ __('No jobs assigned to you yet — check back soon.') }}</flux:text>
            </div>
        @endforelse
    </div>

    @if (count($this->completedJobs) > 0)
        <div class="flex flex-col gap-4">
            <flux:heading size="lg" class="text-primary">{{ __('Recently completed') }}</flux:heading>

            @foreach ($this->completedJobs as $job)
                <div
                    wire:key="completed-{{ $job['id'] }}"
                    class="flex flex-col gap-2 rounded-2xl border border-secondary bg-surface p-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <flux:heading class="text-primary">{{ $job['address'] }}</flux:heading>
                        <flux:text>{{ $job['requested_at']->format('M j, Y') }} &middot; {{ $job['property_size'] }}</flux:text>
                    </div>

                    <flux:badge color="green">{{ __('Completed') }}</flux:badge>
                </div>
            @endforeach
        </div>
    @endif
</div>
