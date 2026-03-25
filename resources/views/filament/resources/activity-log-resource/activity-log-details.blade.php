@php
    $changes = $record->changes ?? [];
    $original = $record->original ?? [];
    $hasChanges = !empty($changes);
@endphp

<div class="space-y-6" dir="auto">
    <!-- Header Card -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-md border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-md flex items-center justify-center {{ $record->action_color === 'success' ? 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400' : ($record->action_color === 'warning' ? 'bg-orange-100 text-orange-600 dark:bg-orange-900 dark:text-orange-400' : ($record->action_color === 'danger' ? 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-400' : 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400')) }}">
                        <x-dynamic-component :component="$record->action_icon" class="w-5 h-5" />
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                        {{ $record->action_label }}
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $record->rendered_message }}
                    </p>
                </div>
                <div class="flex-shrink-0 text-right rtl:text-left">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $record->created_at->translatedFormat('M j, Y') }}
                    </div>
                    <div class="text-xs text-gray-400 dark:text-gray-500">
                        {{ $record->created_at->translatedFormat('g:i A') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Activity Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-md border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('Activity Information') }}
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('User') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                @if($record->user)
                                    <div class="flex items-center space-x-2">
                                        <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                            <span class="text-xs font-medium text-gray-600 dark:text-gray-300">
                                                {{ substr($record->user->name, 0, 2) }}
                                            </span>
                                        </div>
                                        <span>{{ $record->user->name }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">{{ __('System') }}</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Branch') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                @if($record->branch)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-xl text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $record->branch->name }}
                                    </span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">{{ __('N/A') }}</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Model') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-xl text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    {{ $record->model_name }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Record ID') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">
                                #{{ $record->model_id }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Changes Section -->
            @if($hasChanges)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-md border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ __('Changes Made') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('The following properties were modified') }}
                        </p>
                    </div>
                    <div class="overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            {{ __('Property') }}
                                        </th>
                                        <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            {{ __('Before') }}
                                        </th>
                                        <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            {{ __('After') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($changes as $key => $newValue)
                                        @php
                                            $originalValue = $original[$key] ?? null;
                                            
                                            // Format values for display with enum support
                                            $formatValue = function($value, $fieldName = null) use ($record) {
                                                if (is_null($value)) return '<span class="text-gray-400 dark:text-gray-500 italic">' . __('Empty') . '</span>';
                                                
                                                // Check if this field is casted to an enum in the model
                                                if ($fieldName && $record->model_type) {
                                                    try {
                                                        $modelClass = $record->model_type;
                                                        if (class_exists($modelClass)) {
                                                            $model = new $modelClass();
                                                            $casts = $model->getCasts();
                                                            
                                                            if (isset($casts[$fieldName]) && str_contains($casts[$fieldName], 'Enum')) {
                                                                $enumClass = $casts[$fieldName];
                                                                
                                                                // Handle different enum casting formats
                                                                if (str_contains($enumClass, '\\')) {
                                                                    // Full class name like "App\Enums\StatusEnum"
                                                                    $enumClass = str_replace('App\\Enums\\', '', $enumClass);
                                                                    $enumClass = "App\\Enums\\{$enumClass}";
                                                                } else {
                                                                    // Just enum name like "StatusEnum"
                                                                    $enumClass = "App\\Enums\\{$enumClass}";
                                                                }
                                                                
                                                                if (class_exists($enumClass) && enum_exists($enumClass)) {
                                                                    $enumCase = $enumClass::tryFrom($value);
                                                                    if ($enumCase) {
                                                                        // Get enum label and color
                                                                        $label = method_exists($enumCase, 'getLabel') ? $enumCase->getLabel() : $enumCase->value;
                                                                        $color = method_exists($enumCase, 'getColor') ? $enumCase->getColor() : 'gray';
                                                                        
                                                                        // Convert Filament colors to Tailwind classes
                                                                        $colorClass = match($color) {
                                                                            'primary' => 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
                                                                            'success' => 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
                                                                            'warning' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
                                                                            'danger' => 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
                                                                            'info' => 'bg-cyan-100 dark:bg-cyan-900 text-cyan-800 dark:text-cyan-200',
                                                                            'secondary' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
                                                                            default => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
                                                                        };
                                                                        
                                                                        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-xl text-xs font-medium ' . $colorClass . '">' . e($label) . '</span>';
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    } catch (\Exception $e) {
                                                        // Fall back to regular formatting if enum detection fails
                                                    }
                                                }
                                                
                                                if (is_bool($value)) return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-xl text-xs font-medium ' . ($value ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') . '">' . ($value ? __('Yes') : __('No')) . '</span>';
                                                if (is_array($value)) return '<code class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-gray-800 dark:text-gray-200">' . json_encode($value) . '</code>';
                                                return '<span class="text-sm text-gray-900 dark:text-white">' . e($value) . '</span>';
                                            };
                                        @endphp
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap text-left rtl:text-right">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ __(ucfirst(str_replace('_', ' ', $key))) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-left rtl:text-right">
                                                {!! $formatValue($originalValue, $key) !!}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-left rtl:text-right">
                                                {!! $formatValue($newValue, $key) !!}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Technical Details -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-md border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('Technical Details') }}
                    </h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('IP Address') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">
                            {{ $record->ip_address ?? __('N/A') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('User Agent') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white break-all">
                            {{ $record->user_agent ?? __('N/A') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Timestamp') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            <div class="flex items-center space-x-2">
                                <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                                <span>{{ $record->created_at->format('M j, Y g:i A') }}</span>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $record->created_at->diffForHumans() }}
                            </div>
                        </dd>
                    </div>
                </div>
            </div>

            <!-- Action Summary -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-md border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('Action Summary') }}
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-md flex items-center justify-center {{ $record->action_color === 'success' ? 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400' : ($record->action_color === 'warning' ? 'bg-orange-100 text-orange-600 dark:bg-orange-900 dark:text-orange-400' : ($record->action_color === 'danger' ? 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-400' : 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400')) }}">
                                <x-dynamic-component :component="$record->action_icon" class="w-4 h-4" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $record->action_label }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $record->action_description }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if(!$hasChanges)
                <!-- No Changes Message -->
                <div class="bg-gray-50 dark:bg-gray-700 overflow-hidden shadow-sm rounded-md border border-gray-200 dark:border-gray-600">
                    <div class="px-6 py-4 text-center">
                        <x-heroicon-o-document-text class="w-8 h-8 text-gray-400 mx-auto mb-2" />
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('No property changes were recorded for this activity') }}
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
