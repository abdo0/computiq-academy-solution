<x-filament-widgets::widget>
<div class="filament-widget w-full">
    <div class="bg-white rounded-md shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700 w-full">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center">
                    <svg class="h-6 w-6 text-blue-600 me-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM9 17H4l5 5v-5zM9 7H4l5-5v5zM15 7h5l-5-5v5z" />
                    </svg>
                    {{ __('Employee Notifications Overview') }}
                </h2>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Real-time alerts') }}
                </div>
            </div>
            
            <!-- Enhanced Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <!-- Contract Expiry Card -->
                <div class="group relative bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 border border-red-200 dark:border-red-700 rounded-md p-4 hover:shadow-lg transition-all duration-200 hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <div class="p-2 bg-red-100 dark:bg-red-800/40 rounded-md">
                                    <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                            </div>
                            <p class="text-sm font-medium text-red-800 dark:text-red-200 mb-1">{{ __('Contract Expiry') }}</p>
                            <p class="text-3xl font-bold text-red-900 dark:text-red-100">{{ $counts['contract_expiry'] }}</p>
                            <p class="text-xs text-red-600 dark:text-red-300 mt-1">{{ __('expiring soon') }}</p>
                        </div>
                        @if($counts['contract_expiry'] > 0)
                            <div class="absolute top-2 end-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-red-500 rounded-full">
                                    !
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Passport Expiry Card -->
                <div class="group relative bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border border-blue-200 dark:border-blue-700 rounded-md p-4 hover:shadow-lg transition-all duration-200 hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <div class="p-2 bg-blue-100 dark:bg-blue-800/40 rounded-md">
                                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                    </svg>
                                </div>
                            </div>
                            <p class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-1">{{ __('Passport Expiry') }}</p>
                            <p class="text-3xl font-bold text-blue-900 dark:text-blue-100">{{ $counts['passport_expiry'] }}</p>
                            <p class="text-xs text-blue-600 dark:text-blue-300 mt-1">{{ __('expiring soon') }}</p>
                        </div>
                        @if($counts['passport_expiry'] > 0)
                            <div class="absolute top-2 end-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-blue-500 rounded-full">
                                    !
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Iqama Expiry Card -->
                <div class="group relative bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/20 dark:to-amber-800/20 border border-amber-200 dark:border-amber-700 rounded-md p-4 hover:shadow-lg transition-all duration-200 hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <div class="p-2 bg-amber-100 dark:bg-amber-800/40 rounded-md">
                                    <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V4a2 2 0 114 0v2m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                    </svg>
                                </div>
                            </div>
                            <p class="text-sm font-medium text-amber-800 dark:text-amber-200 mb-1">{{ __('Iqama Expiry') }}</p>
                            <p class="text-3xl font-bold text-amber-900 dark:text-amber-100">{{ $counts['iqama_expiry'] }}</p>
                            <p class="text-xs text-amber-600 dark:text-amber-300 mt-1">{{ __('expiring soon') }}</p>
                        </div>
                        @if($counts['iqama_expiry'] > 0)
                            <div class="absolute top-2 end-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-amber-500 rounded-full">
                                    !
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- National ID Expiry Card -->
                <div class="group relative bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 border border-emerald-200 dark:border-emerald-700 rounded-md p-4 hover:shadow-lg transition-all duration-200 hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <div class="p-2 bg-emerald-100 dark:bg-emerald-800/40 rounded-md">
                                    <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                </div>
                            </div>
                            <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200 mb-1">{{ __('National ID Expiry') }}</p>
                            <p class="text-3xl font-bold text-emerald-900 dark:text-emerald-100">{{ $counts['national_id_expiry'] }}</p>
                            <p class="text-xs text-emerald-600 dark:text-emerald-300 mt-1">{{ __('expiring soon') }}</p>
                        </div>
                        @if($counts['national_id_expiry'] > 0)
                            <div class="absolute top-2 end-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-emerald-500 rounded-full">
                                    !
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Driving License Expiry Card -->
                <div class="group relative bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/20 border border-indigo-200 dark:border-indigo-700 rounded-md p-4 hover:shadow-lg transition-all duration-200 hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <div class="p-2 bg-indigo-100 dark:bg-indigo-800/40 rounded-md">
                                    <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                            </div>
                            <p class="text-sm font-medium text-indigo-800 dark:text-indigo-200 mb-1">{{ __('Driving License Expiry') }}</p>
                            <p class="text-3xl font-bold text-indigo-900 dark:text-indigo-100">{{ $counts['driving_license_expiry'] }}</p>
                            <p class="text-xs text-indigo-600 dark:text-indigo-300 mt-1">{{ __('expiring soon') }}</p>
                        </div>
                        @if($counts['driving_license_expiry'] > 0)
                            <div class="absolute top-2 end-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-indigo-500 rounded-full">
                                    !
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Birthdays Card -->
                <div class="group relative bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 border border-purple-200 dark:border-purple-700 rounded-md p-4 hover:shadow-lg transition-all duration-200 hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <div class="p-2 bg-purple-100 dark:bg-purple-800/40 rounded-md">
                                    <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 0v2m-2 0h.01M12 8h.01M12 16h.01M9 16h.01M15 16h.01M9 20h.01M15 20h.01M9 12h.01M15 12h.01" />
                                    </svg>
                                </div>
                            </div>
                            <p class="text-sm font-medium text-purple-800 dark:text-purple-200 mb-1">{{ __('Upcoming Birthdays') }}</p>
                            <p class="text-3xl font-bold text-purple-900 dark:text-purple-100">{{ $counts['birthdays'] }}</p>
                            <p class="text-xs text-purple-600 dark:text-purple-300 mt-1">{{ __('this month') }}</p>
                        </div>
                        @if($counts['birthdays'] > 0)
                            <div class="absolute top-2 end-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-purple-500 rounded-full">
                                    🎂
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Total Summary -->
            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 ">
                        <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded-md">
                            <svg class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Total Active Notifications') }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $counts['total'] }}</p>
                        </div>
                    </div>
                    <div class="text-end">
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Last updated') }}</p>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ now()->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
</div>