<x-filament-panels::page.simple>
    @if (! $this->sent)
        {{ $this->content }}
    @else
        <p class="text-center font-medium" style="color: #16a34a">
            If you have an account, you will receive an email with a link to login shortly.
        </p>
    @endif
</x-filament-panels::page.simple>
