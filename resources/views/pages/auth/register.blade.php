<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form
            method="POST"
            action="{{ route('register.store') }}"
            enctype="multipart/form-data"
            class="flex flex-col gap-6"
            x-data="{ role: '{{ old('role', 'customer') }}' }"
        >
            @csrf

            <!-- Account type -->
            <div class="flex flex-col gap-2">
                <flux:text>{{ __('I am a...') }}</flux:text>
                <div class="flex gap-2">
                    <label class="flex-1 cursor-pointer rounded-xl border px-4 py-2 text-center text-sm" :class="role === 'customer' ? 'border-primary bg-secondary text-primary' : 'border-secondary'">
                        <input type="radio" name="role" value="customer" x-model="role" class="sr-only" />
                        {{ __('Customer') }}
                    </label>
                    <label class="flex-1 cursor-pointer rounded-xl border px-4 py-2 text-center text-sm" :class="role === 'cleaner' ? 'border-primary bg-secondary text-primary' : 'border-secondary'">
                        <input type="radio" name="role" value="cleaner" x-model="role" class="sr-only" />
                        {{ __('Cleaner') }}
                    </label>
                </div>
            </div>

            <!-- Name -->
            <flux:input
                name="name"
                :label="__('Name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Full name')"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Cleaner onboarding fields -->
            <div x-show="role === 'cleaner'" x-cloak class="flex flex-col gap-6">
                <flux:input
                    name="phone"
                    :label="__('Phone number')"
                    :value="old('phone')"
                    type="tel"
                    autocomplete="tel"
                    :placeholder="__('(267) 555-0100')"
                />

                <flux:input
                    name="photo"
                    type="file"
                    accept="image/*"
                    :label="__('Profile photo')"
                    :description="__('This is the only thing customers will ever see about you once you\'re assigned a job.')"
                />
            </div>

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
