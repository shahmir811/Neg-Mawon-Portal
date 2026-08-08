<?php

use App\Enums\JobStatus;
use App\Enums\Role;
use App\Enums\SubscriptionStatus;
use App\Models\CleaningJob;
use App\Models\User;
use App\Notifications\CleanerAssigned;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Admin Dashboard')] class extends Component
{
    /** @var array<int, int> */
    public array $pendingCleaner = [];

    public function assign(int $jobId): void
    {
        $cleanerId = $this->pendingCleaner[$jobId] ?? null;

        if (! $cleanerId) {
            Flux::toast(variant: 'danger', text: __('Select a cleaner first.'));

            return;
        }

        $job = CleaningJob::findOrFail($jobId);

        abort_unless($job->status === JobStatus::Requested, 403);

        $cleaner = User::where('role', Role::Cleaner)->with('cleanerProfile')->findOrFail($cleanerId);

        abort_unless($cleaner->cleanerProfile?->agreement_signed === true, 403);

        $job->update([
            'cleaner_id' => $cleaner->id,
            'status' => JobStatus::Assigned,
        ]);

        $job->customer->notify(new CleanerAssigned($job->fresh()));

        unset($this->pendingCleaner[$jobId]);

        Flux::toast(
            variant: 'success',
            text: __(':cleaner assigned to the job at :address.', ['cleaner' => $cleaner->name, 'address' => $job->address]),
        );
    }

    /**
     * Admin has full visibility (AGENTS.md Section 2 — Admin) — every field
     * here is intentionally broader than the customer/cleaner payloads.
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function jobs(): array
    {
        return CleaningJob::query()
            ->with(['customer.customerProfile', 'cleaner', 'photos'])
            ->latest('requested_at')
            ->get()
            ->map(fn (CleaningJob $job) => [
                ...$job->toPortalArray(),
                'customer_name' => $job->customer->name,
                'customer_phone' => $job->customer->customerProfile?->phone,
                'cleaner_name' => $job->cleaner?->name,
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, label: string}>
     */
    #[Computed]
    public function cleaners(): array
    {
        return User::query()
            ->where('role', Role::Cleaner)
            ->with('cleanerProfile')
            ->whereHas('cleanerProfile', fn ($query) => $query->where('agreement_signed', true))
            ->orderBy('name')
            ->get()
            ->map(fn (User $cleaner) => [
                'id' => $cleaner->id,
                'label' => trim($cleaner->name
                    .($cleaner->cleanerProfile?->subscription_status === SubscriptionStatus::Active ? ' · active' : ' · inactive')),
            ])
            ->all();
    }

    /**
     * @return array{requested: int, assigned: int, completed: int, active_cleaners: int}
     */
    #[Computed]
    public function stats(): array
    {
        return [
            'requested' => CleaningJob::where('status', JobStatus::Requested)->count(),
            'assigned' => CleaningJob::where('status', JobStatus::Assigned)->count(),
            'completed' => CleaningJob::where('status', JobStatus::Completed)->count(),
            'active_cleaners' => User::where('role', Role::Cleaner)
                ->whereHas('cleanerProfile', fn ($query) => $query->where('subscription_status', SubscriptionStatus::Active))
                ->count(),
        ];
    }
}
?>

<div class="flex h-full w-full flex-1 flex-col gap-8">
    <div class="flex flex-col gap-4">
        <span class="inline-flex w-fit items-center rounded-full bg-secondary px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-primary">
            {{ __('Admin') }}
        </span>

        <flux:heading size="xl" class="text-primary">
            {{ __('Welcome back, :name.', ['name' => auth()->user()->name]) }}
        </flux:heading>

        <flux:subheading>
            {{ __('Review job requests and assign a cleaner to each one.') }}
        </flux:subheading>
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-2xl border border-secondary bg-surface p-5">
            <flux:text class="text-text/70">{{ __('Awaiting assignment') }}</flux:text>
            <flux:heading size="xl" class="text-primary">{{ $this->stats['requested'] }}</flux:heading>
        </div>
        <div class="rounded-2xl border border-secondary bg-surface p-5">
            <flux:text class="text-text/70">{{ __('Assigned') }}</flux:text>
            <flux:heading size="xl" class="text-primary">{{ $this->stats['assigned'] }}</flux:heading>
        </div>
        <div class="rounded-2xl border border-secondary bg-surface p-5">
            <flux:text class="text-text/70">{{ __('Completed') }}</flux:text>
            <flux:heading size="xl" class="text-primary">{{ $this->stats['completed'] }}</flux:heading>
        </div>
        <div class="rounded-2xl border border-secondary bg-surface p-5">
            <flux:text class="text-text/70">{{ __('Active cleaners') }}</flux:text>
            <flux:heading size="xl" class="text-primary">{{ $this->stats['active_cleaners'] }}</flux:heading>
        </div>
    </div>

    <div class="flex flex-col gap-4">
        <flux:heading size="lg" class="text-primary">{{ __('Job requests') }}</flux:heading>

        @if (count($this->jobs) > 0)
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Requested') }}</flux:table.column>
                    <flux:table.column>{{ __('Customer') }}</flux:table.column>
                    <flux:table.column>{{ __('Address') }}</flux:table.column>
                    <flux:table.column>{{ __('Property') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Cleaner') }}</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->jobs as $job)
                        <flux:table.row wire:key="job-{{ $job['id'] }}">
                            <flux:table.cell>{{ $job['requested_at']->format('M j, Y g:i A') }}</flux:table.cell>
                            <flux:table.cell>
                                {{ $job['customer_name'] }}
                                @if ($job['customer_phone'])
                                    <flux:text class="text-text/70">{{ $job['customer_phone'] }}</flux:text>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>{{ $job['address'] }}</flux:table.cell>
                            <flux:table.cell>
                                {{ $job['property_size'] }}
                                <div class="mt-1 flex items-center gap-2">
                                    @if ($job['has_pets'])
                                        <flux:icon.paw-print class="size-4 text-text/40" variant="mini" />
                                    @endif
                                    @if ($job['laundry_addon'])
                                        <flux:badge size="sm" color="amber">{{ __('Laundry') }}</flux:badge>
                                    @endif
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="match ($job['status']) {
                                    JobStatus::Requested => 'zinc',
                                    JobStatus::Assigned => 'blue',
                                    JobStatus::Completed => 'green',
                                }">
                                    {{ ucfirst($job['status']->value) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($job['status'] === JobStatus::Requested)
                                    <div class="flex items-center gap-2">
                                        <flux:select wire:model="pendingCleaner.{{ $job['id'] }}" placeholder="{{ __('Select cleaner...') }}" size="sm">
                                            @foreach ($this->cleaners as $cleaner)
                                                <flux:select.option value="{{ $cleaner['id'] }}">{{ $cleaner['label'] }}</flux:select.option>
                                            @endforeach
                                        </flux:select>
                                        <flux:button size="sm" variant="primary" wire:click="assign({{ $job['id'] }})">
                                            {{ __('Assign') }}
                                        </flux:button>
                                    </div>
                                @else
                                    {{ $job['cleaner_name'] }}
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button size="sm" variant="ghost" :href="route('admin.jobs.show', $job['id'])">
                                    {{ __('View details') }}
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <div class="rounded-2xl border border-dashed border-secondary bg-surface p-6 text-center">
                <flux:text>{{ __('No job requests yet.') }}</flux:text>
            </div>
        @endif
    </div>
</div>
