@php
    $changes = $record->changes ?? [];
    $original = $record->original ?? [];
@endphp

@if(!empty($changes))
    <div class="space-y-4">
        <div class="overflow-hidden rounded-md border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('Property') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('Original Value') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('New Value') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($changes as $key => $newValue)
                        @php
                            $originalValue = $original[$key] ?? null;
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ __(ucfirst(str_replace('_', ' ', $key))) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                @if(is_bool($originalValue))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-xl text-xs font-medium {{ $originalValue ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                                        {{ $originalValue ? __('Yes') : __('No') }}
                                    </span>
                                @elseif(is_array($originalValue))
                                    <code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">{{ json_encode($originalValue) }}</code>
                                @else
                                    {{ $originalValue ?? __('N/A') }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                @if(is_bool($newValue))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-xl text-xs font-medium {{ $newValue ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                                        {{ $newValue ? __('Yes') : __('No') }}
                                    </span>
                                @elseif(is_array($newValue))
                                    <code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">{{ json_encode($newValue) }}</code>
                                @else
                                    {{ $newValue ?? __('N/A') }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
        {{ __('No changes recorded') }}
    </div>
@endif
