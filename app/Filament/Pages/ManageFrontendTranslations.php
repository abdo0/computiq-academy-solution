<?php

namespace App\Filament\Pages;

use App\Models\Translation;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Livewire\WithPagination;

class ManageFrontendTranslations extends Page
{
    use WithPagination;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-language';
    }

    public function getTitle(): string
    {
        return __('Translations');
    }

    public static function getNavigationLabel(): string
    {
        return __('Translations');
    }

    public static function getNavigationGroup(): string
    {
        return __('System');
    }

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.manage-frontend-translations';

    public string $selectedLanguage = 'ar';

    public string $search = '';

    public int $perPage = 50;

    public array $languages = [
        'ar' => 'Arabic (العربية)',
        'en' => 'English',
        'ku' => 'Kurdish (کوردی)',
    ];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('add_translation')
                ->label(__('Add Translation Key'))
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->form([
                    TextInput::make('key')
                        ->label(__('Translation Key'))
                        ->required()
                        ->placeholder(__('e.g. nav.home')),
                    Textarea::make('value')
                        ->label(__('Translation Value'))
                        ->required()
                        ->placeholder(__('Enter translation value'))
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    Translation::create($data['key'], $data['value'], $this->selectedLanguage);

                    Notification::make()
                        ->title(__('Translation Added'))
                        ->body(__("Successfully added translation key: {$data['key']}"))
                        ->success()
                        ->send();
                }),
            Action::make('sync_translations')
                ->label(__('Sync Translations'))
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->tooltip(__('Scan project for __() calls and add missing keys'))
                ->action(function () {
                    $this->syncTranslationsFromCode();
                }),
            Action::make('remove_unused')
                ->label(__('Remove Unused'))
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->tooltip(__('Remove unused translation keys'))
                ->requiresConfirmation()
                ->modalHeading(__('Remove Unused Translation Keys'))
                ->modalDescription(__('This will remove all translation keys that are not found in your codebase. This action cannot be undone.'))
                ->action(function () {
                    $this->removeUnusedTranslations();
                }),
        ];
    }

    public function getTranslations(): Collection
    {
        $translations = Translation::all($this->selectedLanguage);

        if ($this->search) {
            $translations = $translations->filter(function ($translation) {
                return stripos($translation->key, $this->search) !== false ||
                       stripos($translation->value, $this->search) !== false;
            });
        }

        return $translations;
    }

    public function getPaginatedTranslations()
    {
        $translations = $this->getTranslations();
        $total = $translations->count();
        $currentPage = $this->getPage();
        $offset = ($currentPage - 1) * $this->perPage;

        return [
            'data' => $translations->slice($offset, $this->perPage)->values(),
            'total' => $total,
            'currentPage' => $currentPage,
            'totalPages' => ceil($total / $this->perPage),
        ];
    }

    public function updatedSelectedLanguage()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updateTranslation($key, $value)
    {
        $translation = Translation::find($key, $this->selectedLanguage);

        if ($translation) {
            $translation->update($value);

            Notification::make()
                ->title(__('Translation Updated'))
                ->body(__("Successfully updated translation key: {$key}"))
                ->success()
                ->send();

            $this->dispatch('$refresh');
        }
    }

    public function deleteTranslation($key)
    {
        $translation = Translation::find($key, $this->selectedLanguage);

        if ($translation) {
            $translation->delete();

            Notification::make()
                ->title(__('Translation Deleted'))
                ->body(__("Successfully deleted translation key: {$key}"))
                ->success()
                ->send();
        }
    }

    protected function syncTranslationsFromCode(): void
    {
        try {
            Artisan::call('sync:translations');

            Notification::make()
                ->title(__('Translations Synced'))
                ->body(__('Successfully synced translation keys from the codebase.'))
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title(__('Sync Failed'))
                ->body(__('Failed to sync translations: ').$e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function removeUnusedTranslations(): void
    {
        try {
            Artisan::call('sync:translations', ['--remove-unused' => true]);

            Notification::make()
                ->title(__('Unused Keys Removed'))
                ->body(__('Successfully removed unused translation keys.'))
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title(__('Cleanup Failed'))
                ->body(__('Failed to remove unused translations: ').$e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function loadTranslationsArray(): array
    {
        return Translation::getAllArray($this->selectedLanguage);
    }

    public function getTranslationsCount(): int
    {
        return count($this->loadTranslationsArray());
    }
}
