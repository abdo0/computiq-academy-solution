<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}
        
        <div class="flex justify-start mt-6">
            <x-filament::button type="submit">
                {{ __('Save changes') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>