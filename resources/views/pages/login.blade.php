<x-filament-panels::page.simple>
    @if(! $this->sent)
        <x-filament-panels::form wire:submit="authenticate">
            {{ $this->form }}

            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
        </x-filament-panels::form>
    @else
        <p class="text-center font-medium" style="color: #16a34a">
            If you have an account, you will receive an email with a link to login shortly.
        </p>
    @endif
</x-filament-panels::page.simple>
