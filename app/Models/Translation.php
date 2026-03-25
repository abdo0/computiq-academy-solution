<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class Translation
{
    public string $key;
    public string $value;
    public string $language;

    public function __construct(string $key, string $value, string $language = 'en')
    {
        $this->key = $key;
        $this->value = $value;
        $this->language = $language;
    }

    public static function all(string $language = 'en'): Collection
    {
        $filePath = lang_path("{$language}.json");

        if (! File::exists($filePath)) {
            return collect();
        }

        $translations = json_decode(File::get($filePath), true) ?? [];

        return collect($translations)->map(function ($value, $key) use ($language) {
            return new self($key, $value, $language);
        });
    }

    public static function find(string $key, string $language = 'en'): ?self
    {
        $translations = self::all($language);

        return $translations->first(fn ($translation) => $translation->key === $key);
    }

    public static function create(string $key, string $value, string $language = 'en'): self
    {
        $translations = self::getAllArray($language);
        $translations[$key] = $value;
        self::saveAll($translations, $language);

        return new self($key, $value, $language);
    }

    public function update(string $newValue): void
    {
        $translations = self::getAllArray($this->language);
        $translations[$this->key] = $newValue;
        self::saveAll($translations, $this->language);

        $this->value = $newValue;
    }

    public function delete(): void
    {
        $translations = self::getAllArray($this->language);
        unset($translations[$this->key]);
        self::saveAll($translations, $this->language);
    }

    public static function getAllArray(string $language): array
    {
        $filePath = lang_path("{$language}.json");

        if (! File::exists($filePath)) {
            return [];
        }

        return json_decode(File::get($filePath), true) ?? [];
    }

    protected static function saveAll(array $translations, string $language): void
    {
        ksort($translations);
        $filePath = lang_path("{$language}.json");
        File::put($filePath, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
