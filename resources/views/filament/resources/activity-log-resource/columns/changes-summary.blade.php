@php
    use Illuminate\Support\Arr;
    use Illuminate\Support\Str;

    $record = $getRecord();
    $properties = $record->properties ?? [];
    $excludedFields = config('activity-log.excluded_fields', []);
    $localeKeys = config('activity-log.locale_keys', []);
    $currentLocale = app()->getLocale();
    $fallbackLocale = config('app.fallback_locale', 'en');
    $action = $record->action->value ?? $record->action;

    $resolveChanges = function () use ($record, $properties) {
        $candidates = [
            $record->changes ?? null,
            $properties['changes'] ?? null,
            $properties['attributes'] ?? null,
            $properties['new'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_array($candidate) && ! empty($candidate)) {
                return $candidate;
            }
        }

        return [];
    };

    $resolveOriginal = function () use ($record, $properties) {
        $candidates = [
            $record->original ?? null,
            $properties['original'] ?? null,
            $properties['old'] ?? null,
            $properties['previous'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_array($candidate) && ! empty($candidate)) {
                return $candidate;
            }
        }

        return [];
    };

    $rawChanges = $resolveChanges();
    $rawOriginal = $resolveOriginal();

    if ($action === 'created') {
        $createdAttributes = $rawChanges;

        if (empty($createdAttributes) && method_exists($record, 'getSubjectModel')) {
            $createdAttributes = $record->getSubjectModel()?->getAttributes() ?? [];
        }

        $filtered = array_diff_key($createdAttributes, array_flip($excludedFields));
        $displayData = array_filter($filtered, fn ($value) => $value !== null && $value !== '' && $value !== '[]' && $value !== '{}');
    } else {
        $filtered = array_diff_key($rawChanges, array_flip($excludedFields));
        $displayData = array_filter($filtered, function ($value) {
            if (is_array($value)) {
                $clean = array_filter($value, fn ($item) => $item !== null && $item !== '' && $item !== '[]' && $item !== '{}');

                return ! empty($clean);
            }

            return $value !== null && $value !== '' && $value !== '[]' && $value !== '{}';
        });
    }

    $changeCount = count($displayData);

    $isTranslatable = function (array $value) use ($localeKeys): bool {
        if (empty($localeKeys)) {
            return false;
        }

        $keys = array_intersect(array_keys($value), $localeKeys);

        if (empty($keys)) {
            return false;
        }

        foreach ($keys as $key) {
            if (! is_string($value[$key])) {
                return false;
            }
        }

        return true;
    };

    $formatValue = function ($value) use ($isTranslatable, $currentLocale, $fallbackLocale) {
        if (is_string($value) && (str_starts_with($value, '{') || str_starts_with($value, '['))) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        if (is_bool($value)) {
            return $value ? __('Yes') : __('No');
        }

        if ($value === null) {
            return __('Empty');
        }

        if (is_array($value) || is_object($value)) {
            $valueArray = (array) $value;

            if ($isTranslatable($valueArray)) {
                $preferredLocales = array_filter([$currentLocale, $fallbackLocale, 'en']);

                foreach ($preferredLocales as $locale) {
                    if (! empty($valueArray[$locale])) {
                        return Str::limit($valueArray[$locale], 60);
                    }
                }

                $first = collect($valueArray)->first(fn ($text) => ! empty($text));

                return $first ? Str::limit($first, 60) : __('Empty');
            }

            return Str::limit(json_encode($valueArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 60);
        }

        $value = (string) $value;

        return Str::limit($value, 60);
    };
@endphp

@if ($changeCount === 0)
    <span class="text-sm text-gray-400 dark:text-gray-500">{{ __('No changes') }}</span>
@elseif ($changeCount === 1 && $action !== 'created')
    @php
        $field = array_key_first($displayData);
        $newValue = Arr::get($displayData, $field);

        $oldValueCandidates = [
            Arr::get($rawOriginal, $field),
            Arr::get($properties, "original.$field"),
            Arr::get($properties, "old.$field"),
            Arr::get($properties, "previous.$field"),
        ];

        $oldValue = Arr::first($oldValueCandidates, fn ($value) => $value !== null && $value !== '');
    @endphp

    <div class="inline-flex items-center gap-1 text-sm">
        <span class="text-gray-500 dark:text-gray-400">
            {{ $formatValue($oldValue) }}
        </span>
        <x-heroicon-o-arrow-long-right class="h-4 w-4 text-gray-400 dark:text-gray-500" />
        <span class="font-medium text-gray-900 dark:text-gray-100">
            {{ $formatValue($newValue) }}
        </span>
    </div>
@else
    <span class="inline-flex items-center justify-center rounded-xl bg-primary-100 px-2 py-0.5 text-xs font-semibold text-primary-700 dark:bg-primary-500/20 dark:text-primary-100">
        {{ $changeCount }}
    </span>
@endif

