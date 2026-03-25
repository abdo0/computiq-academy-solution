<div class="space-y-4" dir="auto">
    <!-- Header Section -->
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3 rtl:space-x-reverse">
                <x-heroicon-o-document-text class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                <div>
                    <div class="flex items-center space-x-2 rtl:space-x-reverse">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Activity Changes') }}
                        </h2>
                        @php
                            $actionConfig = config('activity-log.actions.' . $record->action->value, config('activity-log.actions.default'));
                            $actionColor = $actionConfig['color'];
                            $actionIcon = $actionConfig['icon'];
                        @endphp
                        <span class="inline-flex items-center px-2 py-1 rounded-xl text-xs font-medium {{ $actionColor }}">
                            <x-dynamic-component :component="$actionIcon" class="w-3 h-3 mr-1 rtl:ml-1 rtl:mr-0" />
                            {{ __(ucfirst($record->action->value)) }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ $record->rendered_message }}
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2 rtl:space-x-reverse text-sm text-gray-500 dark:text-gray-400">
                <x-heroicon-o-clock class="w-4 h-4" />
                <span>{{ $record->created_at->format('M j, Y g:i A') }}</span>
            </div>
        </div>
    </div>

    @php
        $isCreatedAction = $record->action->value === 'created';

        // Fields to exclude from display (sensitive or system fields)
        $excludedFields = config('activity-log.excluded_fields', []);

        // Helper function to detect translatable fields
        $isTranslatableField = function($value) {
            if (!is_array($value)) return false;
            
            $localeKeys = config('activity-log.locale_keys', []);
            $hasLocaleKeys = !empty(array_intersect(array_keys($value), $localeKeys));
            
            $allStrings = array_reduce($value, function($carry, $item) {
                return $carry && is_string($item);
            }, true);
            
            $minKeys = config('activity-log.defaults.min_locale_keys_for_translatable', 2);
            $localeKeyCount = count(array_intersect(array_keys($value), $localeKeys));
            
            return $hasLocaleKeys && $allStrings && $localeKeyCount >= $minKeys;
        };
        
        // Helper function to detect simple key-value JSON
        $isSimpleKeyValue = function($value) {
            if (!is_array($value)) return false;
            
            $allSimple = array_reduce($value, function($carry, $item) {
                return $carry && (is_string($item) || is_numeric($item) || is_bool($item));
            }, true);
            
            $maxItems = config('activity-log.defaults.max_simple_key_value_items', 5);
            return $allSimple && count($value) <= $maxItems;
        };

        // Helper function to get model icon
        $getModelIcon = function($modelType) {
            $modelIcons = config('activity-log.model_icons', []);
            return $modelIcons[$modelType] ?? config('activity-log.defaults.model_icon', 'heroicon-o-document-text');
        };

        // Helper function to get field icon
        $getFieldIcon = function($fieldName) {
            $fieldIcons = config('activity-log.field_icons', []);
            return $fieldIcons[$fieldName] ?? config('activity-log.defaults.field_icon', 'heroicon-o-cog-6-tooth');
        };

        // Helper function to resolve relationship values with icons
        $resolveRelationshipValue = function($value, $fieldName, $modelType) use ($getModelIcon) {
            if (is_null($value) || !is_numeric($value)) {
                return $value;
            }

            $relationshipMappings = config('activity-log.relationship_mappings', []);

            if (isset($relationshipMappings[$fieldName])) {
                $mapping = $relationshipMappings[$fieldName];
                $modelClass = $mapping['model'];
                $field = $mapping['field'];

                try {
                    if ($fieldName === 'parent_id' && $modelType) {
                        $modelClass = 'App\\Models\\' . class_basename($modelType);
                    }

                    if (class_exists($modelClass)) {
                        $relatedModel = $modelClass::find($value);
                        if ($relatedModel && isset($relatedModel->$field)) {
                            $icon = $getModelIcon($modelClass);
                            $name = $relatedModel->$field;
                            return [
                                'value' => $name,
                                'icon' => $icon,
                                'is_relationship' => true
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    // If relationship resolution fails, return original value
                }
            }

            return $value;
        };

        if ($isCreatedAction) {
            $allData = $record->subject?->getAttributes() ?? [];
            $filteredData = array_diff_key($allData, array_flip($excludedFields));
            $displayData = array_filter($filteredData, function($value) {
                return !is_null($value) && $value !== '' && $value !== '[]' && $value !== '{}';
            });
            $title = __('Created Properties');
            $countLabel = __('Properties');
        } else {
            $filteredData = array_diff_key($changes, array_flip($excludedFields));
            $displayData = array_filter($filteredData, function($value) {
                return !is_null($value) && $value !== '' && $value !== '[]' && $value !== '{}';
            });
            $title = __('Field Changes');
            $countLabel = __('Changes');
        }
    @endphp

    @if($displayData && count($displayData) > 0)
        <!-- Changes Table -->
        <div class="border border-gray-200 dark:border-gray-700 rounded">
            <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2 rtl:space-x-reverse">
                        <x-heroicon-o-pencil class="w-3 h-3 text-gray-500 dark:text-gray-400" />
                        <h3 class="text-xs font-medium text-gray-900 dark:text-gray-100">
                            {{ $title }}
                        </h3>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ count($displayData) }} {{ $countLabel }}
                    </span>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-3 py-2 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ __('Field') }}
                            </th>
                            @if($isCreatedAction)
                                <th class="px-3 py-2 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ __('Value') }}
                                </th>
                            @else
                                <th class="px-3 py-2 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ __('Before') }}
                                </th>
                                <th class="px-3 py-2 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ __('After') }}
                                </th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ __('Status') }}
                                </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                        @foreach($displayData as $field => $value)
                            @php
                                if ($isCreatedAction) {
                                    $newValue = $value;
                                    $oldValue = null;
                                } else {
                                    $newValue = $value;
                                    $oldValue = $original[$field] ?? null;
                                }
                                
                                // Resolve relationship values for both old and new values
                                $resolvedOldValue = $resolveRelationshipValue($oldValue, $field, $record->model_type);
                                $resolvedNewValue = $resolveRelationshipValue($newValue, $field, $record->model_type);
                                
                                // Format values for display
                                $formatValue = function($value, $fieldName = null) use ($record, $isTranslatableField, $isSimpleKeyValue, $resolveRelationshipValue, $getModelIcon, $getFieldIcon) {
                                    if (is_null($value)) return '<span class="text-gray-400 text-xs">' . __('Empty') . '</span>';
                                    
                                    // Resolve relationship values first
                                    $resolvedValue = $resolveRelationshipValue($value, $fieldName, $record->model_type);
                                    
                                    // Handle relationship values with icons
                                    if (is_array($resolvedValue) && isset($resolvedValue['is_relationship']) && $resolvedValue['is_relationship']) {
                                        $icon = $resolvedValue['icon'];
                                        $name = $resolvedValue['value'];
                                        return '<div class="flex items-center space-x-1 rtl:space-x-reverse">
                                                    <x-dynamic-component :component="$icon" class="w-3 h-3 text-gray-500 dark:text-gray-400" />
                                                    <span class="text-gray-600 dark:text-gray-300 text-xs">' . e($name) . '</span>
                                                </div>';
                                    }
                                    
                                    // Handle JSON strings
                                    if (is_string($resolvedValue) && (str_starts_with($resolvedValue, '{') || str_starts_with($resolvedValue, '['))) {
                                        $decodedValue = json_decode($resolvedValue, true);
                                        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedValue)) {
                                            $resolvedValue = $decodedValue;
                                        }
                                    }
                                    
                                    // Handle translatable columns
                                    if (is_array($resolvedValue) && $isTranslatableField($resolvedValue)) {
                                        $currentLocale = app()->getLocale();
                                        $translatedValue = $resolvedValue[$currentLocale] ?? $resolvedValue['en'] ?? array_values($resolvedValue)[0] ?? '';
                                        
                                        if (empty($translatedValue)) {
                                            return '<span class="text-gray-400 text-xs">' . __('Empty') . '</span>';
                                        }
                                        
                                        return '<span class="text-gray-600 dark:text-gray-300 text-xs">' . e($translatedValue) . '</span>';
                                    }
                                    
                                    // Handle JSON fields intelligently
                                    if (is_array($resolvedValue) || is_object($resolvedValue)) {
                                        $jsonValue = is_object($resolvedValue) ? (array) $resolvedValue : $resolvedValue;
                                        
                                        if ($isSimpleKeyValue($jsonValue)) {
                                            $formatted = [];
                                            foreach ($jsonValue as $key => $val) {
                                                $formatted[] = '<strong>' . e($key) . ':</strong> ' . e($val);
                                            }
                                            return '<div class="text-xs space-y-1">' . implode('<br>', $formatted) . '</div>';
                                        }
                                        
                                        $count = count($jsonValue);
                                        return '<span class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">' . 
                                               __('JSON Object') . ' (' . $count . ' ' . __('Items') . ')' . 
                                               '</span>';
                                    }
                                    
                                    // Check if this field is casted to an enum
                                    if ($fieldName && $record->model_type) {
                                        try {
                                            $modelClass = $record->model_type;
                                            if (class_exists($modelClass)) {
                                                $model = new $modelClass();
                                                $casts = $model->getCasts();
                                                
                                                if (isset($casts[$fieldName]) && str_contains($casts[$fieldName], 'Enum')) {
                                                    $enumClass = $casts[$fieldName];
                                                    
                                                    if (class_exists($enumClass) && enum_exists($enumClass)) {
                                                        $enumCase = $enumClass::tryFrom($resolvedValue);
                                                        if ($enumCase) {
                                                            $label = method_exists($enumCase, 'getLabel') ? $enumCase->getLabel() : $enumCase->value;
                                                            $color = method_exists($enumCase, 'getColor') ? $enumCase->getColor() : 'gray';
                                                            
                                                            $colorClass = config('activity-log.enum_colors.' . $color, config('activity-log.enum_colors.default'));
                                                            
                                                            return '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium ' . $colorClass . '">' . e($label) . '</span>';
                                                        }
                                                    }
                                                }
                                            }
                                        } catch (\Exception $e) {
                                            // Fall back to regular formatting
                                        }
                                    }
                                    
                                    if (is_bool($resolvedValue)) return $resolvedValue ? '<span class="text-green-600 dark:text-green-400 text-xs">' . __('Yes') . '</span>' : '<span class="text-red-600 dark:text-red-400 text-xs">' . __('No') . '</span>';
                                    $maxLength = config('activity-log.defaults.max_string_length', 50);
                                    if (is_string($resolvedValue) && strlen($resolvedValue) > $maxLength) return '<span class="text-gray-600 dark:text-gray-300 text-xs">' . e(substr($resolvedValue, 0, $maxLength)) . '...</span>';
                                    return '<span class="text-gray-600 dark:text-gray-300 text-xs">' . e($resolvedValue) . '</span>';
                                };
                                
                                // Determine change status
                                $changeStatus = 'modified';
                                $oldValueForComparison = is_array($resolvedOldValue) && isset($resolvedOldValue['is_relationship']) ? $resolvedOldValue['value'] : $resolvedOldValue;
                                $newValueForComparison = is_array($resolvedNewValue) && isset($resolvedNewValue['is_relationship']) ? $resolvedNewValue['value'] : $resolvedNewValue;
                                
                                if (is_null($oldValueForComparison) && !is_null($newValueForComparison)) {
                                    $changeStatus = 'added';
                                } elseif (!is_null($oldValueForComparison) && is_null($newValueForComparison)) {
                                    $changeStatus = 'removed';
                                }
                            @endphp
                            
                            <tr>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="flex items-center space-x-2 rtl:space-x-reverse">
                                        <x-dynamic-component :component="$getFieldIcon($field)" class="w-3 h-3 text-gray-500 dark:text-gray-400" />
                                        <span class="text-xs font-medium text-gray-900 dark:text-gray-100">
                                            {{ __(ucfirst(str_replace('_', ' ', $field))) }}
                                        </span>
                                    </div>
                                </td>
                                @if($isCreatedAction)
                                    <td class="px-3 py-2 text-xs">
                                        <div class="max-w-xs">
                                            {!! $formatValue($resolvedNewValue, $field) !!}
                                        </div>
                                    </td>
                                @else
                                    <td class="px-3 py-2 text-xs">
                                        <div class="max-w-xs">
                                            {!! $formatValue($resolvedOldValue, $field) !!}
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 text-xs">
                                        <div class="max-w-xs">
                                            {!! $formatValue($resolvedNewValue, $field) !!}
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        @if($changeStatus === 'added')
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ config('activity-log.change_status_colors.added', 'bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-200') }}">
                                                <x-heroicon-o-plus class="w-2 h-2 mr-1 rtl:ml-1 rtl:mr-0" />
                                                {{ __('Added') }}
                                            </span>
                                        @elseif($changeStatus === 'removed')
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ config('activity-log.change_status_colors.removed', 'bg-red-100 dark:bg-red-800 text-red-800 dark:text-red-200') }}">
                                                <x-heroicon-o-minus class="w-2 h-2 mr-1 rtl:ml-1 rtl:mr-0" />
                                                {{ __('Removed') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ config('activity-log.change_status_colors.modified', 'bg-yellow-100 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-200') }}">
                                                <x-heroicon-o-pencil class="w-2 h-2 mr-1 rtl:ml-1 rtl:mr-0" />
                                                {{ __('Modified') }}
                                            </span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <!-- No Changes State -->
        <div class="text-center py-8">
            <x-heroicon-o-document-text class="w-12 h-12 text-gray-400 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                {{ __('No Changes') }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ __('This activity did not involve any field changes.') }}
            </p>
        </div>
    @endif
    
    <!-- Activity Details -->
    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <div class="flex items-center space-x-2 rtl:space-x-reverse mb-3">
            <x-heroicon-o-information-circle class="w-4 h-4 text-gray-500 dark:text-gray-400" />
            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Activity Details') }}</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div class="space-y-3">
                <div class="flex items-center space-x-2 rtl:space-x-reverse">
                    <x-heroicon-o-user class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                    <div>
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ __('User:') }}</span>
                        <span class="text-gray-600 dark:text-gray-300 ml-2 rtl:mr-2 rtl:ml-0">
                            {{ $record->user?->name ?? __('System') }}
                        </span>
                    </div>
                </div>
                @if(class_exists(\App\Models\Branch::class) && method_exists($record, 'branch'))
                <div class="flex items-center space-x-2 rtl:space-x-reverse">
                    <x-heroicon-o-building-office-2 class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                    <div>
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ __('Branch:') }}</span>
                        <span class="text-gray-600 dark:text-gray-300 ml-2 rtl:mr-2 rtl:ml-0">
                            {{ $record->branch?->name ?? __('N/A') }}
                        </span>
                    </div>
                </div>
                @endif
                <div class="flex items-center space-x-2 rtl:space-x-reverse">
                    <x-dynamic-component :component="$getModelIcon($record->model_type)" class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                    <div>
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ __('Model:') }}</span>
                        <span class="text-gray-600 dark:text-gray-300 ml-2 rtl:mr-2 rtl:ml-0">
                            {{ $record->model_type ? __(class_basename($record->model_type)) : __('Unknown') }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center space-x-2 rtl:space-x-reverse">
                    <x-heroicon-o-hashtag class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                    <div>
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ __('Record ID:') }}</span>
                        <span class="text-gray-600 dark:text-gray-300 ml-2 rtl:mr-2 rtl:ml-0">
                            {{ $record->model_id ?? __('N/A') }}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="space-y-3">
                <div class="flex items-center space-x-2 rtl:space-x-reverse">
                    <x-heroicon-o-globe-alt class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                    <div>
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ __('IP Address:') }}</span>
                        <span class="text-gray-600 dark:text-gray-300 ml-2 rtl:mr-2 rtl:ml-0 font-mono text-xs">
                            {{ $record->ip_address ?? __('N/A') }}
                        </span>
                    </div>
                </div>
                <div class="flex items-start space-x-2 rtl:space-x-reverse">
                    <x-heroicon-o-computer-desktop class="w-4 h-4 text-gray-500 dark:text-gray-400 mt-0.5" />
                    <div class="flex-1">
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ __('User Agent:') }}</span>
                        <div class="text-gray-600 dark:text-gray-300 mt-1 text-xs font-mono break-all">
                            {{ $record->user_agent ?? __('N/A') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
