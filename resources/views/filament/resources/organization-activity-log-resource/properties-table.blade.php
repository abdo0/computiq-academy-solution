@php
    $record = $getRecord();
    $properties = $record->properties ?? [];
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
    
    function formatValue($value, $key = null) {
        if (is_null($value)) {
            return '<span class="text-gray-400 dark:text-gray-500 italic">' . __('N/A') . '</span>';
        }
        
        if (is_bool($value)) {
            $colorClass = $value ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-xl text-xs font-medium ' . $colorClass . '">' . ($value ? __('Yes') : __('No')) . '</span>';
        }
        
        if (is_array($value)) {
            return '<div class="bg-gray-100 dark:bg-gray-700 p-2 rounded overflow-auto max-h-40"><pre class="text-xs text-gray-700 dark:text-gray-300" dir="ltr">' . e(json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre></div>';
        }
        
        if (in_array($key, ['created_at', 'updated_at', 'deleted_at', 'date', 'timestamp']) || isDate($value)) {
            return '<span class="text-sm text-gray-900 dark:text-white">' . e(formatDate($value)) . '</span>';
        }
        
        if (in_array($key, ['status', 'action', 'type'])) {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-xl text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">' . e(__($value)) . '</span>';
        }
        
        return '<span class="text-sm text-gray-900 dark:text-white">' . e($value) . '</span>';
    }
@endphp

@if(empty($properties))
    <div class="bg-gray-50 dark:bg-gray-700 rounded-md p-6 text-center">
        <x-heroicon-o-document-text class="w-8 h-8 text-gray-400 mx-auto mb-2" />
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('No properties recorded for this activity') }}
        </p>
    </div>
@else
    <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm rounded-md border border-gray-200 dark:border-gray-700" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 w-full">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700">
                    <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Property') }}</th>
                    <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Value') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($properties as $key => $value)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 {{ $loop->even ? 'bg-gray-50 dark:bg-gray-700' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            {{ __(ucfirst(str_replace('_', ' ', $key))) }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            {!! formatValue($value, $key) !!}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
