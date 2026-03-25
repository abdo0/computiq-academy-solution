<?php

namespace App\Providers;

use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\ServiceProvider;

class FilamentConfigProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerAssets();
        $this->configureLanguageSwitch();
        $this->configureFormComponents();
        $this->configureSchemaComponents();
        $this->configureTableComponents();
        $this->configureFilterComponents();
    }

    /**
     * Register Filament assets (JS, CSS)
     */
    protected function registerAssets(): void
    {
        // Fix menu scrolling issue
        FilamentAsset::register(
            assets: [
                Js::make('filament/menu-scroll-fix-v4', __DIR__.'/../../public/js/app/filament/menu-scroll-fix-v4.js'),
            ],
        );
    }

    /**
     * Configure language switcher
     */
    protected function configureLanguageSwitch(): void
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['ar', 'en', 'ku']); // also accepts a closure
        });
    }

    /**
     * Configure default settings for form input components
     */
    protected function configureFormComponents(): void
    {
        // TextInput defaults
        TextInput::configureUsing(function (TextInput $textInput) {
            $textInput
                ->maxLength(255); // Default max length for text inputs
        });

        // Select defaults
        Select::configureUsing(function (Select $select) {
            $select
                ->preload()     // Preload options for better UX
                ->native(false); // Use custom select UI
        });

        // DatePicker defaults
        DatePicker::configureUsing(function (DatePicker $datePicker) {
            $datePicker
                ->native(false)  // Use custom date picker UI
                ->displayFormat('Y-m-d'); // Standard date format
        });

        // Textarea defaults
        Textarea::configureUsing(function (Textarea $textarea) {
            $textarea
                ->rows(3)        // Default 3 rows
                ->autosize()     // Auto-resize based on content
                ->maxLength(1000); // Default max length
        });

        // Repeater defaults
        Repeater::configureUsing(function (Repeater $repeater) {
            $repeater
                ->reorderable()     // Allow reordering items
                ->collapsible()     // Make items collapsible
                ->defaultItems(1)   // Start with 1 item
                ->columnSpanFull(); // Span full width by default
        });
    }

    /**
     * Configure default settings for schema components (layouts)
     */
    protected function configureSchemaComponents(): void
    {
        // Section defaults
        Section::configureUsing(function (Section $section) {
            $section
                ->columns(2); // Default 2 columns for sections
        });
    }

    /**
     * Configure default settings for table components
     */
    protected function configureTableComponents(): void
    {
        // Table defaults
        Table::configureUsing(function (Table $table): void {
            $table
                ->deferColumnManager(false)
                ->deferFilters(false)
                // ->persistFiltersInSession()
                ->persistSortInSession()
                // ->persistSearchInSession()
                // ->persistColumnSearchesInSession()
                ->striped()
                ->defaultSort('created_at', 'desc')
                ->reorderableColumns()
                ->filtersLayout(FiltersLayout::AboveContent)
                ->paginationPageOptions([10, 25, 50]);
        });

        // TextColumn defaults
        TextColumn::configureUsing(function (TextColumn $column) {
            $column
                ->sortable()     // Make columns sortable by default
                ->toggleable();   // Allow users to toggle visibility
        });
    }

    /**
     * Configure default settings for filter components
     */
    protected function configureFilterComponents(): void
    {
        // SelectFilter defaults
        SelectFilter::configureUsing(function (SelectFilter $filter) {
            $filter
                ->native(false)
                ->preload();      // Preload options
        });
    }
}
