@php
    $flags = $getRecord()->compliance_flags ?? [];
    $groupedFlags = collect($flags)->groupBy('severity');
@endphp

<div class="space-y-4">
    @if(empty($flags))
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No compliance flags') }}</p>
    @else
        @foreach(['critical' => 'danger', 'high' => 'danger', 'medium' => 'warning', 'low' => 'info'] as $severity => $color)
            @if($groupedFlags->has($severity))
                <div>
                    <h4 class="text-sm font-semibold mb-2 capitalize">{{ __($severity) }} {{ __('Flags') }}</h4>
                    <div class="space-y-2">
                        @foreach($groupedFlags->get($severity) as $flag)
                            <div class="p-3 rounded-md border border-{{ $color }}-200 dark:border-{{ $color }}-800 bg-{{ $color }}-50 dark:bg-{{ $color }}-900/20">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-{{ $color }}-800 dark:text-{{ $color }}-200">
                                            {{ $flag['flag'] ?? __('Unknown Flag') }}
                                        </p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                            {{ $flag['reason'] ?? __('No reason provided') }}
                                        </p>
                                        @if(isset($flag['created_at']))
                                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                {{ __('Flagged on') }}: {{ \Carbon\Carbon::parse($flag['created_at'])->format('Y-m-d H:i:s') }}
                                            </p>
                                        @endif
                                    </div>
                                    <span class="ml-2 px-2 py-1 text-xs font-semibold rounded bg-{{ $color }}-100 dark:bg-{{ $color }}-900 text-{{ $color }}-800 dark:text-{{ $color }}-200">
                                        {{ ucfirst($severity) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    @endif
</div>

