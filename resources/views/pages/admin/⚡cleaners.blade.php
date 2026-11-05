<?php

use App\Enums\AgreementStatus;
use App\Enums\JobStatus;
use App\Enums\Role;
use App\Enums\SubscriptionStatus;
use App\Models\User;
use App\Support\StorageUrl;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Cleaners')] class extends Component
{
    use WithFileUploads;

    public bool $showAgreementModal = false;

    public ?int $uploadingCleanerId = null;

    public bool $showPartnerInfoModal = false;

    public ?int $viewingPartnerInfoId = null;

    public function viewPartnerInfo(int $cleanerId): void
    {
        $this->viewingPartnerInfoId = $cleanerId;
        $this->showPartnerInfoModal = true;
    }

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $agreementPhoto = null;

    public function openAgreementUpload(int $cleanerId): void
    {
        $this->uploadingCleanerId = $cleanerId;
        $this->agreementPhoto = null;
        $this->showAgreementModal = true;
    }

    public function uploadAgreement(): void
    {
        $this->validate([
            'agreementPhoto' => ['required', 'image', 'max:5120'],
        ]);

        $cleaner = User::where('role', Role::Cleaner)->findOrFail($this->uploadingCleanerId);

        $cleaner->cleanerProfile()->update([
            'agreement_photo_path' => $this->agreementPhoto->store('agreements', 'public'),
            'agreement_signed' => true,
        ]);

        $this->reset(['agreementPhoto', 'showAgreementModal', 'uploadingCleanerId']);

        Flux::toast(
            variant: 'success',
            text: __(":name's signed agreement has been saved.", ['name' => $cleaner->name]),
        );
    }

    /**
     * James reviews the photo the cleaner submitted and approves it —
     * no re-upload needed, since the cleaner already provided the file.
     */
    public function approveAgreement(int $cleanerId): void
    {
        $cleaner = User::where('role', Role::Cleaner)->findOrFail($cleanerId);

        abort_unless($cleaner->cleanerProfile?->agreement_photo_path, 403);

        $cleaner->cleanerProfile->update(['agreement_signed' => true]);

        Flux::toast(
            variant: 'success',
            text: __(":name's agreement has been approved.", ['name' => $cleaner->name]),
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function cleaners(): array
    {
        return User::query()
            ->where('role', Role::Cleaner)
            ->with('cleanerProfile')
            ->withCount([
                'jobsAsCleaner as assigned_jobs_count' => fn ($query) => $query->where('status', JobStatus::Assigned),
                'jobsAsCleaner as completed_jobs_count' => fn ($query) => $query->where('status', JobStatus::Completed),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (User $cleaner) => [
                'id' => $cleaner->id,
                'name' => $cleaner->name,
                'email' => $cleaner->email,
                'phone' => $cleaner->cleanerProfile?->phone,
                'zip_code' => $cleaner->cleanerProfile?->zip_code,
                'photo_url' => StorageUrl::for($cleaner->cleanerProfile?->photo_path),
                'service_area' => $cleaner->cleanerProfile?->service_area,
                'experience' => $cleaner->cleanerProfile?->experience,
                'services_offered' => $cleaner->cleanerProfile?->services_offered ?? [],
                'availability' => $cleaner->cleanerProfile?->availability,
                'business_name' => $cleaner->cleanerProfile?->business_name,
                'insurance_provider' => $cleaner->cleanerProfile?->insurance_provider,
                'agreement_status' => $cleaner->cleanerProfile?->agreementStatus() ?? AgreementStatus::NotSubmitted,
                'agreement_photo_url' => StorageUrl::for($cleaner->cleanerProfile?->agreement_photo_path),
                'subscription_status' => $cleaner->cleanerProfile?->subscription_status,
                'subscription_plan' => $cleaner->cleanerProfile?->subscription_plan,
                'next_renewal_at' => $cleaner->cleanerProfile?->next_renewal_at,
                'assigned_jobs_count' => $cleaner->assigned_jobs_count,
                'completed_jobs_count' => $cleaner->completed_jobs_count,
            ])
            ->all();
    }

    #[Computed]
    public function uploadingCleaner(): ?array
    {
        if (! $this->uploadingCleanerId) {
            return null;
        }

        return collect($this->cleaners)->firstWhere('id', $this->uploadingCleanerId);
    }

    #[Computed]
    public function viewingPartnerInfo(): ?array
    {
        if (! $this->viewingPartnerInfoId) {
            return null;
        }

        return collect($this->cleaners)->firstWhere('id', $this->viewingPartnerInfoId);
    }
}
?>

<div class="flex h-full w-full flex-1 flex-col gap-8">
    <div class="flex flex-col gap-4">
        <span class="inline-flex w-fit items-center rounded-full bg-secondary px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-primary">
            {{ __('Admin') }}
        </span>

        <flux:heading size="xl" class="text-primary">{{ __('Cleaners') }}</flux:heading>

        <flux:subheading>
            {{ __('Agreement and subscription status for everyone onboarded to the platform.') }}
        </flux:subheading>
    </div>

    @if (count($this->cleaners) > 0)
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Contact') }}</flux:table.column>
                <flux:table.column>{{ __('Agreement') }}</flux:table.column>
                <flux:table.column>{{ __('Subscription') }}</flux:table.column>
                <flux:table.column>{{ __('Active jobs') }}</flux:table.column>
                <flux:table.column>{{ __('Completed') }}</flux:table.column>
                <flux:table.column>{{ __('Partner Info') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->cleaners as $cleaner)
                    <flux:table.row wire:key="cleaner-{{ $cleaner['id'] }}">
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:avatar :src="$cleaner['photo_url']" icon="user" size="sm" />
                                {{ $cleaner['name'] }}
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $cleaner['email'] }}
                            @if ($cleaner['phone'])
                                <flux:text class="text-text/70">{{ $cleaner['phone'] }}</flux:text>
                            @endif
                            @if ($cleaner['zip_code'])
                                <flux:text class="text-text/70">{{ $cleaner['zip_code'] }}</flux:text>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                @if ($cleaner['agreement_photo_url'])
                                    <a href="{{ $cleaner['agreement_photo_url'] }}" target="_blank" class="block h-10 w-10 shrink-0 overflow-hidden rounded-lg border border-secondary">
                                        <img src="{{ $cleaner['agreement_photo_url'] }}" class="h-full w-full object-cover" />
                                    </a>
                                @endif

                                <div class="flex flex-col items-start gap-1.5">
                                    <flux:badge
                                        :color="match ($cleaner['agreement_status']) {
                                            \App\Enums\AgreementStatus::Approved => 'green',
                                            \App\Enums\AgreementStatus::Pending => 'blue',
                                            \App\Enums\AgreementStatus::NotSubmitted => 'amber',
                                        }"
                                    >
                                        {{ match ($cleaner['agreement_status']) {
                                            \App\Enums\AgreementStatus::Approved => __('Approved'),
                                            \App\Enums\AgreementStatus::Pending => __('Pending review'),
                                            \App\Enums\AgreementStatus::NotSubmitted => __('Not on file'),
                                        } }}
                                    </flux:badge>

                                    <div class="flex items-center gap-1">
                                        @if ($cleaner['agreement_status'] === \App\Enums\AgreementStatus::Pending)
                                            <flux:button size="sm" variant="primary" wire:click="approveAgreement({{ $cleaner['id'] }})">
                                                {{ __('Approve') }}
                                            </flux:button>
                                        @endif

                                        <flux:button size="sm" variant="ghost" wire:click="openAgreementUpload({{ $cleaner['id'] }})">
                                            {{ $cleaner['agreement_photo_url'] ? __('Replace photo') : __('Upload photo') }}
                                        </flux:button>
                                    </div>
                                </div>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="$cleaner['subscription_status'] === SubscriptionStatus::Active ? 'green' : 'zinc'">
                                {{ $cleaner['subscription_status'] === SubscriptionStatus::Active ? __('Active') : __('Inactive') }}
                            </flux:badge>
                            @if ($cleaner['subscription_plan'])
                                <flux:text class="text-text/70">
                                    {{ ucfirst($cleaner['subscription_plan']->value) }}
                                    @if ($cleaner['next_renewal_at'])
                                        &middot; {{ __('renews :date', ['date' => $cleaner['next_renewal_at']->format('M j, Y')]) }}
                                    @endif
                                </flux:text>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>{{ $cleaner['assigned_jobs_count'] }}</flux:table.cell>
                        <flux:table.cell>{{ $cleaner['completed_jobs_count'] }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:button size="sm" variant="ghost" wire:click="viewPartnerInfo({{ $cleaner['id'] }})">
                                {{ __('View') }}
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @else
        <div class="rounded-2xl border border-dashed border-secondary bg-surface p-6 text-center">
            <flux:text>{{ __('No cleaners onboarded yet.') }}</flux:text>
        </div>
    @endif

    <flux:modal wire:model.self="showAgreementModal" class="w-full max-w-md">
        @if ($this->uploadingCleaner)
            <form wire:submit="uploadAgreement" class="flex flex-col gap-4">
                <flux:heading size="lg" class="text-primary">
                    {{ __("Upload :name's signed agreement", ['name' => $this->uploadingCleaner['name']]) }}
                </flux:heading>

                <flux:text class="text-text/70">
                    {{ __('Cleaners normally submit their own signed agreement from their Profile settings for you to approve. Use this only as a fallback — for example if a cleaner sent you the photo outside the app. Uploading here marks it approved immediately.') }}
                </flux:text>

                @if ($this->uploadingCleaner['agreement_photo_url'])
                    <img src="{{ $this->uploadingCleaner['agreement_photo_url'] }}" class="h-32 w-32 rounded-xl border border-secondary object-cover" />
                @endif

                <flux:input
                    type="file"
                    wire:model="agreementPhoto"
                    :label="__('Signed agreement photo')"
                    accept="image/*"
                />

                @if ($agreementPhoto)
                    <img src="{{ $agreementPhoto->temporaryUrl() }}" class="h-32 w-32 rounded-xl object-cover" />
                @endif

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="$set('showAgreementModal', false)">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ __('Save agreement') }}
                    </flux:button>
                </div>
            </form>
        @endif
    </flux:modal>

    <flux:modal wire:model.self="showPartnerInfoModal" class="w-full max-w-md">
        @if ($this->viewingPartnerInfo)
            <div class="flex flex-col gap-4">
                <flux:heading size="lg" class="text-primary">
                    {{ __(":name's partner info", ['name' => $this->viewingPartnerInfo['name']]) }}
                </flux:heading>

                <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                    <div>
                        <flux:text class="text-text/60">{{ __('Service area') }}</flux:text>
                        <flux:text class="text-primary">{{ $this->viewingPartnerInfo['service_area'] ?? __('Not provided') }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-text/60">{{ __('Availability') }}</flux:text>
                        <flux:text class="text-primary">{{ $this->viewingPartnerInfo['availability'] ?? __('Not provided') }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-text/60">{{ __('Business name') }}</flux:text>
                        <flux:text class="text-primary">{{ $this->viewingPartnerInfo['business_name'] ?? __('Not provided') }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-text/60">{{ __('Insurance provider') }}</flux:text>
                        <flux:text class="text-primary">{{ $this->viewingPartnerInfo['insurance_provider'] ?? __('Not provided') }}</flux:text>
                    </div>
                </div>

                <div>
                    <flux:text class="text-text/60">{{ __('Experience') }}</flux:text>
                    <flux:text class="whitespace-pre-line text-primary">{{ $this->viewingPartnerInfo['experience'] ?? __('Not provided') }}</flux:text>
                </div>

                <div>
                    <flux:text class="mb-1 block text-text/60">{{ __('Services offered') }}</flux:text>
                    @if (count($this->viewingPartnerInfo['services_offered']) > 0)
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($this->viewingPartnerInfo['services_offered'] as $serviceType)
                                <flux:badge>{{ \App\Enums\ServiceType::from($serviceType)->label() }}</flux:badge>
                            @endforeach
                        </div>
                    @else
                        <flux:text class="text-primary">{{ __('Not provided') }}</flux:text>
                    @endif
                </div>
            </div>
        @endif
    </flux:modal>
</div>
