<x-filament-panels::page.simple>
    <div class="flex items-center justify-center w-full">
        <div class="w-full max-w-md">
            @if (settings('login_image'))
                <div class="flex justify-center mb-6">
                    <img src="{{ settings('login_image') }}" 
                         alt="{{ settings('app_name', 'HR System') }}" 
                         class="max-w-xs max-h-40 object-contain">
                </div>
            @endif
            
            <div class="mb-8 text-center">
                {{-- <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ __('Welcome to') }} {{ settings('app_name', 'HR System') }}
                </h1> --}}
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    {{ __('Please sign in to your account') }}
                </p>
            </div>

            <form wire:submit="authenticate">
                {{ $this->form }}

                <div class="flex justify-start mt-6">
                    <x-filament::button type="submit" class="w-full">
                        {{ __('Sign In') }}
                    </x-filament::button>
                </div>
            </form>
            
            @if ($this->getSubheading())
                <div class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
                    {!! $this->getSubheading() !!}
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page.simple>