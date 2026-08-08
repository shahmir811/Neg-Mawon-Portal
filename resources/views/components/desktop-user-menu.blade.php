<flux:dropdown position="bottom" align="start">
    <button
        type="button"
        class="group flex w-full items-center gap-2 rounded-lg p-1 text-start hover:bg-secondary/60"
        data-test="sidebar-menu-button"
    >
        <flux:avatar
            :src="auth()->user()->avatarUrl()"
            :name="auth()->user()->name"
            :initials="auth()->user()->initials()"
            size="sm"
            class="shrink-0"
        />

        <div class="in-data-flux-sidebar-collapsed-desktop:hidden grid flex-1 text-start leading-tight">
            <span class="truncate text-sm font-medium text-primary">{{ auth()->user()->name }}</span>
            <span class="truncate text-xs text-text/60">{{ ucfirst(auth()->user()->role->value) }}</span>
        </div>

        <flux:icon.chevrons-up-down
            variant="micro"
            class="in-data-flux-sidebar-collapsed-desktop:hidden ms-auto size-4 shrink-0 text-text/40"
        />
    </button>

    <flux:menu>
        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
            <flux:avatar
                :src="auth()->user()->avatarUrl()"
                :name="auth()->user()->name"
                :initials="auth()->user()->initials()"
            />
            <div class="grid flex-1 text-start text-sm leading-tight">
                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
            </div>
        </div>
        <flux:menu.separator />
        <flux:menu.radio.group>
            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                {{ __('Settings') }}
            </flux:menu.item>
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item
                    as="button"
                    type="submit"
                    icon="arrow-right-start-on-rectangle"
                    class="w-full cursor-pointer"
                    data-test="logout-button"
                >
                    {{ __('Log out') }}
                </flux:menu.item>
            </form>
        </flux:menu.radio.group>
    </flux:menu>
</flux:dropdown>
