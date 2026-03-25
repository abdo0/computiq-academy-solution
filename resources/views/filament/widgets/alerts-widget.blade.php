<x-filament-widgets::widget>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ __('Alerts & Urgent Actions') }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ __('Items requiring immediate attention') }}
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Last updated: ') }}{{ now()->format('H:i') }}
                </span>
                <button onclick="refreshAlerts()" 
                        class="inline-flex items-center p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-all duration-200">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Alerts Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            @foreach($this->getAlerts() as $alert)
                <a href="{{ $alert['url'] }}" class="block group">
                    <div class="bg-white dark:bg-gray-900 border-2 border-gray-200 dark:border-gray-700 rounded-md p-5 hover:shadow-lg hover:scale-[1.02] transition-all duration-300 cursor-pointer relative overflow-hidden
                         @if($alert['count'] > 0) border-l-4 border-l-{{ $alert['color'] }}-500 @endif">
                        
                        {{-- Background gradient on hover --}}
                        <div class="absolute inset-0 bg-gradient-to-br from-{{ $alert['color'] }}-50/0 to-{{ $alert['color'] }}-50/0 dark:from-{{ $alert['color'] }}-900/0 dark:to-{{ $alert['color'] }}-900/0 group-hover:from-{{ $alert['color'] }}-50/50 group-hover:to-{{ $alert['color'] }}-50/30 dark:group-hover:from-{{ $alert['color'] }}-900/20 dark:group-hover:to-{{ $alert['color'] }}-900/10 transition-all duration-300"></div>
                        
                        <div class="relative z-10">
                            {{-- Icon and Count --}}
                            <div class="flex items-center justify-between mb-3">
                                <div class="w-10 h-10 bg-{{ $alert['color'] }}-100 dark:bg-{{ $alert['color'] }}-900/50 rounded-md flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-200">
                                    <x-filament::icon :icon="$alert['icon']" class="h-5 w-5 text-{{ $alert['color'] }}-600 dark:text-{{ $alert['color'] }}-400" />
                                </div>
                                <div class="text-right">
                                    <div class="text-3xl font-bold text-gray-900 dark:text-white
                                        @if($alert['count'] > 0) text-{{ $alert['color'] }}-600 dark:text-{{ $alert['color'] }}-400 @endif">
                                        {{ number_format($alert['count']) }}
                                    </div>
                                </div>
                            </div>

                            {{-- Title --}}
                            <div class="text-sm font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-{{ $alert['color'] }}-600 dark:group-hover:text-{{ $alert['color'] }}-400 transition-colors">
                                {{ $alert['title'] }}
                            </div>

                            {{-- Status --}}
                            <div class="mt-3">
                                @if($alert['count'] > 0)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-medium bg-{{ $alert['color'] }}-100 text-{{ $alert['color'] }}-800 dark:bg-{{ $alert['color'] }}-900/50 dark:text-{{ $alert['color'] }}-200 shadow-sm">
                                        {{ __('Action Required') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 shadow-sm">
                                        {{ __('All Clear') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Quick Actions --}}
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 border-2 border-blue-200 dark:border-gray-600 rounded-md p-5 shadow-sm">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-600 dark:bg-blue-500 rounded-md flex items-center justify-center shadow-sm">
                        <x-heroicon-o-cog-6-tooth class="h-5 w-5 text-white" />
                    </div>
                    <div>
                        <h4 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('Quick Actions') }}</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">{{ __('Handle urgent items quickly') }}</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('filament.admin.resources.users.create') }}"
                       class="inline-flex items-center px-4 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 shadow-sm hover:shadow">
                        <x-heroicon-o-user-plus class="h-4 w-4 mr-1.5" />
                        {{ __('Add User') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function refreshAlerts() {
            // Refresh the widget data
            window.location.reload();
        }
    </script>
</x-filament-widgets::widget>
