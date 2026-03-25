@php
    $verification = $getRecord()->verification;
    $documents = [
        'registration_certificate_path' => __('Registration Certificate'),
        'tax_certificate_path' => __('Tax Certificate'),
        'bank_statement_path' => __('Bank Statement'),
        'board_resolution_path' => __('Board Resolution'),
    ];
@endphp

<div class="space-y-3">
    @if(!$verification)
        <p class="text-sm text-warning-600 dark:text-warning-400">{{ __('No verification record found') }}</p>
    @else
        @foreach($documents as $field => $label)
            @php
                $path = $verification->$field;
                $exists = !empty($path);
                $isExpired = $exists && $verification->submitted_at && $verification->submitted_at->diffInMonths(now()) > 12;
            @endphp
            <div class="flex items-center justify-between p-2 rounded border {{ $exists ? 'border-success-200 dark:border-success-800 bg-success-50 dark:bg-success-900/20' : 'border-danger-200 dark:border-danger-800 bg-danger-50 dark:bg-danger-900/20' }}">
                <div class="flex items-center space-x-2">
                    @if($exists)
                        <svg class="w-5 h-5 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-danger-600 dark:text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @endif
                    <span class="text-sm font-medium {{ $exists ? 'text-success-800 dark:text-success-200' : 'text-danger-800 dark:text-danger-200' }}">
                        {{ $label }}
                    </span>
                </div>
                <div class="flex items-center space-x-2">
                    @if($exists)
                        @if($isExpired)
                            <span class="px-2 py-1 text-xs font-semibold rounded bg-warning-100 dark:bg-warning-900 text-warning-800 dark:text-warning-200">
                                {{ __('Expired') }}
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded bg-success-100 dark:bg-success-900 text-success-800 dark:text-success-200">
                                {{ __('Valid') }}
                            </span>
                        @endif
                        @if($path)
                            <a href="{{ asset('storage/'.$path) }}" target="_blank" class="text-xs text-primary-600 dark:text-primary-400 hover:underline">
                                {{ __('View') }}
                            </a>
                        @endif
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded bg-danger-100 dark:bg-danger-900 text-danger-800 dark:text-danger-200">
                            {{ __('Missing') }}
                        </span>
                    @endif
                </div>
            </div>
        @endforeach

        @if($verification->submitted_at)
            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    {{ __('Submitted') }}: {{ $verification->submitted_at->format('Y-m-d') }}
                    @if($verification->submitted_at->diffInMonths(now()) > 12)
                        <span class="text-warning-600 dark:text-warning-400 ml-2">
                            ({{ __('Over 12 months ago - may be expired') }})
                        </span>
                    @endif
                </p>
            </div>
        @endif
    @endif
</div>

