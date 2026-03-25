<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class SyncTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:translations {--dry-run : Show what would be updated without making changes} {--remove-unused : Remove translation keys that are not used anywhere in the codebase} {--report : Show completion percentage report} {--retranslate-all : Force AI retranslation of all keys regardless of current translation status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync translation labels from JSX/TSX/Blade/PHP files to frontend translation JSON files. Use --remove-unused to clean up old keys. Use --retranslate-all to AI-translate everything.';

    /**
     * Translation files and their paths
     *
     * @var array
     */
    protected $translationLocales = ['ar', 'en', 'ku'];

    /**
     * Directories to scan
     *
     * @var array
     */
    protected $directoriesToScan = [
        'resources/js',
        'resources/views',
        'app/Http',
        'app/Filament',
        'app/Mail',
        'app/Models',
        'app/Services',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Starting full project translation sync...');

        // Extract all translation keys from JSX, TSX, Blade, and PHP files
        $extractedKeys = $this->extractTranslationKeys();

        if (empty($extractedKeys)) {
            $this->warn('⚠️  No translation keys found in the scanned files.');

            return 0;
        }

        $this->info(sprintf('📝 Found %d unique translation keys in the codebase', count($extractedKeys)));

        // Process each translation file
        foreach ($this->translationLocales as $locale) {
            $this->processTranslationFile($locale, $extractedKeys);
        }

        if ($this->option('report')) {
            $this->generateCompletenessReport($extractedKeys);
        }

        if ($this->option('dry-run')) {
            $this->info('🔍 Dry run completed. No JSON files were actually modified.');
        } else {
            $this->info('✅ Translation sync completed successfully!');
        }

        return 0;
    }

    /**
     * Extract translation keys from all selected directories
     */
    protected function extractTranslationKeys(): array
    {
        $keys = [];

        foreach ($this->directoriesToScan as $directory) {
            $path = base_path($directory);

            if (! File::exists($path)) {
                $this->warn(sprintf('⚠️  Directory not found: %s', $path));

                continue;
            }

            // Get all files recursively
            $files = File::allFiles($path);
            $scannableFiles = collect($files)->filter(function ($file) {
                return in_array($file->getExtension(), ['jsx', 'js', 'tsx', 'ts', 'php']);
            });

            foreach ($scannableFiles as $file) {
                $content = File::get($file->getPathname());
                $fileKeys = $this->extractKeysFromContent($content, $file->getRelativePathname());
                $keys = array_merge($keys, $fileKeys);
            }
        }

        // Remove duplicates and sort
        $keys = array_unique($keys);
        sort($keys);

        return $keys;
    }

    /**
     * Extract translation keys using regex patterns for React __(), PHP __(), trans(), and @lang()
     */
    protected function extractKeysFromContent(string $content, string $filename): array
    {
        $keys = [];

        $patterns = [
            // __('Key') or __("Key")
            '/__\([\'"]([^\'"]*)[\'"](?:,|\))/u',
            // __(`key`) - template literals
            '/__\(`([^`]*)`(?:,|\))/u',
            // trans('key') or trans("key")
            '/trans\([\'"]([^\'"]*)[\'"](?:,|\))/u',
            // @lang('key') or @lang("key")
            '/@lang\([\'"]([^\'"]*)[\'"]\)/u',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                foreach ($matches[1] as $key) {
                    // Skip empty keys
                    if (! empty(trim($key))) {
                        $keys[] = trim($key);
                        $this->line(sprintf('📄 %s: "%s"', $filename, $key), null, 'vv');
                    }
                }
            }
        }

        return $keys;
    }

    /**
     * Process a single JSON translation file
     */
    protected function processTranslationFile(string $locale, array $extractedKeys): void
    {
        $filePath = base_path("lang/{$locale}.json");
        $this->info(sprintf('🔄 Processing %s.json translation file...', strtoupper($locale)));

        $existingTranslations = $this->loadExistingTranslations($filePath);

        $newKeys = [];
        $removedKeys = [];
        $keysToTranslate = [];
        $updatedTranslations = $existingTranslations;
        $retranslateAll = $this->option('retranslate-all');

        // Identify missing or untranslated keys
        foreach ($extractedKeys as $key) {
            $isMissing = ! array_key_exists($key, $existingTranslations);
            $isUntranslated = $isMissing || $existingTranslations[$key] === $key;

            // Should we translate this key?
            if (($isUntranslated || $retranslateAll) && ! is_numeric($key) && strlen($key) > 1 && $key !== strtoupper($key)) {
                $keysToTranslate[] = $key;
            }

            if ($isMissing) {
                $updatedTranslations[$key] = $key; // Default value before translation
                $newKeys[] = $key;
                $this->line(sprintf('➕ Added: "%s"', $key), null, 'v');
            }
        }

        // Perform AI Translation if needed
        if (count($keysToTranslate) > 0 && $locale !== 'en') {
            $translatedMap = $this->translateMissingKeys($locale, $keysToTranslate);
            foreach ($translatedMap as $enKey => $translatedText) {
                if (isset($updatedTranslations[$enKey]) && ! empty($translatedText)) {
                    $updatedTranslations[$enKey] = (string) $translatedText;
                }
            }
        }

        // Always handle unused key removal
        $unusedKeys = array_diff(array_keys($existingTranslations), $extractedKeys);

        foreach ($unusedKeys as $unusedKey) {
            unset($updatedTranslations[$unusedKey]);
            $removedKeys[] = $unusedKey;
            $this->line(sprintf('🗑️  Removed (unused): "%s"', $unusedKey), null, 'v');
        }

        if (count($removedKeys) > 0) {
            $this->info(sprintf('📉 Found %d unused translation keys', count($removedKeys)));
        }

        // Sort keys alphabetically for clean diffs
        ksort($updatedTranslations);

        // Save the file if there are changes
        $hasChanges = count($newKeys) > 0 || count($removedKeys) > 0 || count($keysToTranslate) > 0;

        if ($hasChanges) {
            if (! $this->option('dry-run')) {
                $this->saveTranslationFile($filePath, $updatedTranslations);
            }

            if (count($newKeys) > 0) {
                $this->info(sprintf('📊 Added %d new translation keys', count($newKeys)));
            }
            if (count($removedKeys) > 0) {
                $this->info(sprintf('📊 Removed %d unused translation keys', count($removedKeys)));
            }
        } else {
            $this->info('📊 No changes needed (already up-to-date)');
        }

        $this->info(sprintf('📊 Total keys in %s.json: %d', $locale, count($updatedTranslations)));
    }

    /**
     * Translate keys using Gemini AI
     */
    protected function translateMissingKeys(string $locale, array $keysToTranslate): array
    {
        $apiKey = env('VITE_GEMINI_API_KEY') ?? env('GEMINI_API_KEY');
        if (! $apiKey) {
            $this->warn('⚠️ No Gemini API key found in .env. Skipping AI translation.');

            return [];
        }

        $langName = $locale === 'ar' ? 'Arabic' : 'Kurdish (Sorani)';
        $this->info('⏳ Translating '.count($keysToTranslate)." keys to {$langName} using Gemini AI...");

        $translatedResults = [];
        $chunks = array_chunk($keysToTranslate, 200);

        foreach ($chunks as $index => $chunk) {
            $this->info('   - Translating batch '.($index + 1).'/'.count($chunks).'...');

            $prompt = "You are an expert software localization translator. Translate the following English UI strings into natural, contextually accurate {$langName}.
Rules:
1. Maintain the exact same tone (professional, encouraging, e-learning platform context).
2. DO NOT translate placeholders starting with \":\" (like :count, :name) or wrapped in brackets (like {name}). Keep them EXACTLY as they are.
3. Return ONLY a valid JSON object where keys are the original English strings and values are the {$langName} translations. Do not include markdown blocks.

Strings to translate:
".json_encode($chunk, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            try {
                $response = Http::timeout(120)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'responseMimeType' => 'application/json',
                    ],
                ]);

                if ($response->successful()) {
                    $JSONresponse = $response->json();
                    if (isset($JSONresponse['candidates'][0]['content']['parts'][0]['text'])) {
                        $text = $JSONresponse['candidates'][0]['content']['parts'][0]['text'];
                        $text = preg_replace('/```json\s*|\s*```/', '', $text);
                        $translatedBatch = json_decode(trim($text), true);
                        if (is_array($translatedBatch)) {
                            $translatedResults = array_merge($translatedResults, $translatedBatch);
                        } else {
                            $this->error('❌ Failed to parse JSON from AI response.');
                        }
                    }
                } else {
                    $errorData = $response->json();
                    $errorMsg = $errorData['error']['message'] ?? 'Unknown Error';
                    $this->error("❌ Failed to translate batch: {$errorMsg}");
                    // Stop translating further if API key is invalid
                    if (strpos($errorMsg, 'API_KEY_INVALID') !== false || strpos($errorMsg, 'expired') !== false) {
                        break;
                    }
                }
            } catch (\Exception $e) {
                $this->error('❌ Request failed: '.$e->getMessage());
            }
        }

        return $translatedResults;
    }

    /**
     * Generate completeness report (% translated)
     */
    protected function generateCompletenessReport(array $extractedKeys): void
    {
        $this->info("\n📊 Translation Completeness Report");
        $this->info(str_repeat('-', 40));

        foreach ($this->translationLocales as $locale) {
            $filePath = base_path("lang/{$locale}.json");
            $translations = $this->loadExistingTranslations($filePath);

            $translatedCount = 0;
            $totalKeys = count($extractedKeys);

            if ($totalKeys === 0) {
                continue;
            }

            foreach ($extractedKeys as $key) {
                // Determine if a key is "translated" if it exists and value !== key
                // (except for English, where value === key is often the correct translation)
                if (array_key_exists($key, $translations) && ! empty($translations[$key])) {
                    if ($locale === 'en' || $translations[$key] !== $key) {
                        $translatedCount++;
                    }
                }
            }

            $percentage = round(($translatedCount / $totalKeys) * 100, 1);

            $color = 'green';
            if ($percentage < 50) {
                $color = 'red';
            } elseif ($percentage < 90) {
                $color = 'yellow';
            }

            $this->info(sprintf('<fg=%s>%-5s</> | %d / %d keys | %5.1f%% Complete',
                $color, strtoupper($locale), $translatedCount, $totalKeys, $percentage));
        }
        $this->info(str_repeat('-', 40)."\n");
    }

    /**
     * Load existing translations from JSON
     */
    protected function loadExistingTranslations(string $filePath): array
    {
        if (! File::exists($filePath)) {
            return [];
        }

        $content = File::get($filePath);
        $translations = json_decode($content, true);

        return (json_last_error() === JSON_ERROR_NONE && is_array($translations)) ? $translations : [];
    }

    /**
     * Save translations to JSON securely
     */
    protected function saveTranslationFile(string $filePath, array $translations): void
    {
        $directory = dirname($filePath);
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $json = json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        File::put($filePath, $json);
        $this->line(sprintf('💾 Saved: %s', $filePath));
    }
}
