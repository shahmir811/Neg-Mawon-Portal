<?php

use App\Concerns\ProfileValidationRules;
use App\Enums\AgreementStatus;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules, WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $zip_code = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $photo = null;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $agreementPhoto = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->zip_code = Auth::user()->cleanerProfile?->zip_code ?? '';
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    /**
     * Update the cleaner's profile photo — the only thing a customer ever
     * sees about their assigned cleaner (AGENTS.md Section 5).
     */
    public function updatePhoto(): void
    {
        abort_unless(Auth::user()->isCleaner(), 403);

        $this->validate(['photo' => ['required', 'image', 'max:5120']]);

        $profile = Auth::user()->cleanerProfile;

        if ($profile->photo_path) {
            Storage::disk('public')->delete($profile->photo_path);
        }

        $profile->update(['photo_path' => $this->photo->store('cleaners', 'public')]);

        $this->reset('photo');

        Flux::toast(variant: 'success', text: __('Profile photo updated.'));
    }

    /**
     * Zip code is just a proximity hint for James when he's manually
     * picking a cleaner to assign — not used for any automated matching.
     */
    public function updateZipCode(): void
    {
        abort_unless(Auth::user()->isCleaner(), 403);

        $this->validate(['zip_code' => ['nullable', 'string', 'max:10']]);

        Auth::user()->cleanerProfile->update(['zip_code' => $this->zip_code]);

        Flux::toast(variant: 'success', text: __('Zip code updated.'));
    }

    /**
     * Cleaner uploads a photo of their hand-signed exclusivity agreement.
     * This only marks it pending — James still has to approve it before
     * `agreement_signed` flips to true (AGENTS.md Section 6). Any new
     * upload resets an already-approved agreement back to pending, since
     * it's a fresh document James hasn't reviewed yet.
     */
    public function uploadAgreement(): void
    {
        abort_unless(Auth::user()->isCleaner(), 403);

        $this->validate(['agreementPhoto' => ['required', 'image', 'max:5120']]);

        $profile = Auth::user()->cleanerProfile;

        if ($profile->agreement_photo_path) {
            Storage::disk('public')->delete($profile->agreement_photo_path);
        }

        $profile->update([
            'agreement_photo_path' => $this->agreementPhoto->store('agreements', 'public'),
            'agreement_signed' => false,
        ]);

        $this->reset('agreementPhoto');

        Flux::toast(variant: 'success', text: __('Agreement submitted — James will review it shortly.'));
    }

    #[Computed]
    public function isCleaner(): bool
    {
        return Auth::user()->isCleaner();
    }

    #[Computed]
    public function currentPhotoUrl(): ?string
    {
        $photoPath = Auth::user()->cleanerProfile?->photo_path;

        return $photoPath ? Storage::url($photoPath) : null;
    }

    #[Computed]
    public function currentAgreementPhotoUrl(): ?string
    {
        $photoPath = Auth::user()->cleanerProfile?->agreement_photo_path;

        return $photoPath ? Storage::url($photoPath) : null;
    }

    #[Computed]
    public function agreementStatus(): AgreementStatus
    {
        return Auth::user()->cleanerProfile?->agreementStatus() ?? AgreementStatus::NotSubmitted;
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        {{ __('Save') }}
                    </flux:button>
                </div>

            </div>
        </form>

        @if ($this->isCleaner)
            <div class="my-6 w-full space-y-4 border-t border-secondary pt-6">
                <div>
                    <flux:heading>{{ __('Profile photo') }}</flux:heading>
                    <flux:text class="text-text/70">
                        {{ __('This is the only thing customers will ever see about you once you\'re assigned a job.') }}
                    </flux:text>
                </div>

                <div class="flex items-center gap-4">
                    <flux:avatar
                        :src="$photo ? $photo->temporaryUrl() : $this->currentPhotoUrl"
                        icon="user"
                        size="xl"
                        class="shrink-0"
                    />

                    <form wire:submit="updatePhoto" class="flex flex-1 flex-col items-start gap-3">
                        <flux:input type="file" wire:model="photo" accept="image/*" />

                        <flux:button type="submit" variant="primary" size="sm">
                            {{ __('Save photo') }}
                        </flux:button>
                    </form>
                </div>
            </div>

            <div class="my-6 w-full space-y-4 border-t border-secondary pt-6">
                <div>
                    <flux:heading>{{ __('Service area') }}</flux:heading>
                    <flux:text class="text-text/70">
                        {{ __('Optional — helps James pick a nearby cleaner when assigning jobs.') }}
                    </flux:text>
                </div>

                <form wire:submit="updateZipCode" class="flex items-end gap-3">
                    <flux:input wire:model="zip_code" :label="__('Zip code')" placeholder="19111" class="max-w-xs" />
                    <flux:button type="submit" variant="primary" size="sm">{{ __('Save') }}</flux:button>
                </form>
            </div>

            <div class="my-6 w-full space-y-4 border-t border-secondary pt-6">
                <div>
                    <flux:heading>{{ __('Exclusivity agreement') }}</flux:heading>
                    <flux:text class="text-text/70">
                        {{ __('Upload a photo of your signed exclusivity agreement. James reviews it before it counts as on file.') }}
                    </flux:text>
                </div>

                <flux:badge
                    :color="match ($this->agreementStatus) {
                        \App\Enums\AgreementStatus::Approved => 'green',
                        \App\Enums\AgreementStatus::Pending => 'blue',
                        \App\Enums\AgreementStatus::NotSubmitted => 'amber',
                    }"
                    class="w-fit"
                >
                    {{ match ($this->agreementStatus) {
                        \App\Enums\AgreementStatus::Approved => __('Approved'),
                        \App\Enums\AgreementStatus::Pending => __('Pending review'),
                        \App\Enums\AgreementStatus::NotSubmitted => __('Not submitted'),
                    } }}
                </flux:badge>

                <div class="flex items-center gap-4">
                    @if ($agreementPhoto)
                        <img src="{{ $agreementPhoto->temporaryUrl() }}" class="h-20 w-20 shrink-0 rounded-xl border border-secondary object-cover" />
                    @elseif ($this->currentAgreementPhotoUrl)
                        <a href="{{ $this->currentAgreementPhotoUrl }}" target="_blank" class="block h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-secondary">
                            <img src="{{ $this->currentAgreementPhotoUrl }}" class="h-full w-full object-cover" />
                        </a>
                    @endif

                    <form wire:submit="uploadAgreement" class="flex flex-1 flex-col items-start gap-3">
                        <flux:input type="file" wire:model="agreementPhoto" accept="image/*" />

                        <flux:button type="submit" variant="primary" size="sm">
                            {{ $this->currentAgreementPhotoUrl ? __('Submit new photo') : __('Submit for review') }}
                        </flux:button>
                    </form>
                </div>
            </div>
        @endif

        @if ($this->showDeleteUser)
            <livewire:pages::settings.delete-user-form />
        @endif
    </x-pages::settings.layout>
</section>
