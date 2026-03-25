@php
    $record = $getRecord();
    $properties = $record->properties;
    $isRtl = app()->getLocale() === 'ar' || app()->getLocale() === 'ku';
    
    // Helper function to check if a value is a date and format it
    function isDate($value) {
        if (!is_string($value)) return false;
        try {
            return \Carbon\Carbon::parse($value) !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    function formatDate($value) {
        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return $value;
        }
    }
@endphp

<div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm rounded-md" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 w-full">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-700">
                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Field') }}</th>
                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Previous Value') }}</th>
                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('New Value') }}</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($properties['attributes'] as $key => $newValue)
                <tr class="{{ $loop->even ? 'bg-gray-50 dark:bg-gray-700' : '' }}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                        {{ __(ucfirst(str_replace('_', ' ', $key))) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        @if(isset($properties['old'][$key]))
                            @if(is_array($properties['old'][$key]))
                                <div class="bg-gray-100 dark:bg-gray-700 p-2 rounded overflow-auto max-h-40">
                                    <pre class="text-xs text-gray-700 dark:text-gray-300" dir="ltr">{{ json_encode($properties['old'][$key], JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            @elseif(is_null($properties['old'][$key]))
                                <span class="text-gray-400 dark:text-gray-500 italic">{{ __('Null') }}</span>
                            @elseif(in_array($key, ['created_at', 'updated_at', 'deleted_at']) || isDate($properties['old'][$key]))
                                {{ formatDate($properties['old'][$key]) }}
                            @elseif(in_array($key, ['status', 'action', 'type']))
                                {{ __($properties['old'][$key]) }}
                            @else
                                {{ $properties['old'][$key] }}
                            @endif
                        @else
                            <span class="text-gray-400 dark:text-gray-500 italic">{{ __('Null') }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        @if(is_array($newValue))
                            <div class="bg-gray-100 dark:bg-gray-700 p-2 rounded overflow-auto max-h-40">
                                <pre class="text-xs text-gray-700 dark:text-gray-300" dir="ltr">{{ json_encode($newValue, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @elseif(is_null($newValue))
                            <span class="text-gray-400 dark:text-gray-500 italic">{{ __('Null') }}</span>
                        @elseif(in_array($key, ['created_at', 'updated_at', 'deleted_at']) || isDate($newValue))
                            {{ formatDate($newValue) }}
                        @elseif(in_array($key, ['status', 'action', 'type']))
                            {{ __($newValue) }}
                        @else
                            {{ $newValue }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
