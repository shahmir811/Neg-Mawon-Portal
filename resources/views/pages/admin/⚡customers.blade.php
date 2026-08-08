<?php

use App\Enums\JobStatus;
use App\Enums\Role;
use App\Models\CleaningJob;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Customers')] class extends Component
{
    public bool $showJobsModal = false;

    public ?int $viewingCustomerId = null;

    public function viewJobs(int $customerId): void
    {
        $this->viewingCustomerId = $customerId;
        $this->showJobsModal = true;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function customers(): array
    {
        return User::query()
            ->where('role', Role::Customer)
            ->with('customerProfile')
            ->withCount([
                'jobsAsCustomer as requested_jobs_count' => fn ($query) => $query->where('status', JobStatus::Requested),
                'jobsAsCustomer as assigned_jobs_count' => fn ($query) => $query->where('status', JobStatus::Assigned),
                'jobsAsCustomer as completed_jobs_count' => fn ($query) => $query->where('status', JobStatus::Completed),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (User $customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->customerProfile?->phone,
                'requested_jobs_count' => $customer->requested_jobs_count,
                'assigned_jobs_count' => $customer->assigned_jobs_count,
                'completed_jobs_count' => $customer->completed_jobs_count,
            ])
            ->all();
    }

    #[Computed]
    public function viewingCustomer(): ?array
    {
        if (! $this->viewingCustomerId) {
            return null;
        }

        return collect($this->customers)->firstWhere('id', $this->viewingCustomerId);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function viewingCustomerJobs(): array
    {
        if (! $this->viewingCustomerId) {
            return [];
        }

        return CleaningJob::query()
            ->where('customer_id', $this->viewingCustomerId)
            ->with('photos')
            ->latest('requested_at')
            ->get()
            ->map(fn (CleaningJob $job) => $job->toPortalArray())
            ->all();
    }
}
?>

<div class="flex h-full w-full flex-1 flex-col gap-8">
    <div class="flex flex-col gap-4">
        <span class="inline-flex w-fit items-center rounded-full bg-secondary px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-primary">
            {{ __('Admin') }}
        </span>

        <flux:heading size="xl" class="text-primary">{{ __('Customers') }}</flux:heading>

        <flux:subheading>
            {{ __('Everyone who has created an account, and their job history.') }}
        </flux:subheading>
    </div>

    @if (count($this->customers) > 0)
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Contact') }}</flux:table.column>
                <flux:table.column>{{ __('Requested') }}</flux:table.column>
                <flux:table.column>{{ __('Assigned') }}</flux:table.column>
                <flux:table.column>{{ __('Completed') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->customers as $customer)
                    <flux:table.row wire:key="customer-{{ $customer['id'] }}">
                        <flux:table.cell>{{ $customer['name'] }}</flux:table.cell>
                        <flux:table.cell>
                            {{ $customer['email'] }}
                            @if ($customer['phone'])
                                <flux:text class="text-text/70">{{ $customer['phone'] }}</flux:text>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $customer['requested_jobs_count'] }}</flux:table.cell>
                        <flux:table.cell>{{ $customer['assigned_jobs_count'] }}</flux:table.cell>
                        <flux:table.cell>{{ $customer['completed_jobs_count'] }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:button size="sm" variant="ghost" wire:click="viewJobs({{ $customer['id'] }})">
                                {{ __('View jobs') }}
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @else
        <div class="rounded-2xl border border-dashed border-secondary bg-surface p-6 text-center">
            <flux:text>{{ __('No customer accounts yet.') }}</flux:text>
        </div>
    @endif

    <flux:modal wire:model.self="showJobsModal" class="w-full max-w-2xl">
        @if ($this->viewingCustomer)
            <div class="flex flex-col gap-4">
                <flux:heading size="lg" class="text-primary">
                    {{ __(":name's job history", ['name' => $this->viewingCustomer['name']]) }}
                </flux:heading>

                @forelse ($this->viewingCustomerJobs as $job)
                    <div wire:key="viewing-job-{{ $job['id'] }}" class="flex flex-col gap-2 rounded-2xl border border-secondary bg-surface p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <flux:heading class="text-primary">
                                {{ $job['address'] }}
                                <flux:link :href="$job['google_maps_url']" target="_blank" class="text-sm font-normal">
                                    {{ __('View on map') }}
                                </flux:link>
                            </flux:heading>
                            <flux:text>{{ $job['requested_at']->format('M j, Y \a\t g:i A') }} &middot; {{ $job['property_size'] }}</flux:text>
                            @if (count($job['photo_urls']) > 0)
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($job['photo_urls'] as $photoUrl)
                                        <img src="{{ $photoUrl }}" class="h-14 w-14 rounded-lg object-cover" />
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <flux:badge :color="match ($job['status']) {
                            JobStatus::Requested => 'zinc',
                            JobStatus::Assigned => 'blue',
                            JobStatus::Completed => 'green',
                        }">
                            {{ ucfirst($job['status']->value) }}
                        </flux:badge>
                    </div>
                @empty
                    <flux:text>{{ __('No jobs requested yet.') }}</flux:text>
                @endforelse
            </div>
        @endif
    </flux:modal>
</div>
