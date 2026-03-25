<?php

namespace App\Filament\Exports;

use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;

class UserExporter extends Exporter
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label(__('ID'))
                ->formatStateUsing(fn ($state) => '#'.$state),

            ExportColumn::make('name')
                ->label(__('Full Name'))
                ->formatStateUsing(fn ($state) => $state ?: __('No name')),

            ExportColumn::make('employment_id')
                ->label(__('Employment ID'))
                ->formatStateUsing(fn ($state) => $state ?: __('Not assigned')),

            ExportColumn::make('email')
                ->label(__('Email'))
                ->formatStateUsing(fn ($state) => $state ?: __('No email')),

            ExportColumn::make('phone')
                ->label(__('Phone'))
                ->formatStateUsing(fn ($state) => $state ?: __('No phone')),

            ExportColumn::make('mobile')
                ->label(__('Mobile'))
                ->formatStateUsing(fn ($state) => $state ?: __('No mobile')),

            ExportColumn::make('skype_id')
                ->label(__('Skype ID'))
                ->formatStateUsing(fn ($state) => $state ?: __('No Skype ID')),

            ExportColumn::make('user_type')
                ->label(__('User Type'))
                ->formatStateUsing(fn ($state) => $state ?: __('Not specified')),

            ExportColumn::make('locale')
                ->label(__('Locale'))
                ->formatStateUsing(fn ($state) => $state ?: __('Not set')),

            ExportColumn::make('language')
                ->label(__('Language'))
                ->formatStateUsing(fn ($state) => $state ?: __('Not set')),

            ExportColumn::make('direction')
                ->label(__('Text Direction'))
                ->formatStateUsing(fn ($state) => $state ?: __('Not set')),

            ExportColumn::make('is_active')
                ->label(__('Active'))
                ->state(function (User $record): string {
                    return $record->is_active ? __('Yes') : __('No');
                }),

            ExportColumn::make('email_verified_at')
                ->label(__('Email Verified'))
                ->formatStateUsing(fn ($state) => $state ? $state->format('Y-m-d H:i') : __('Not verified')),

            ExportColumn::make('last_activity_at')
                ->label(__('Last Activity'))
                ->formatStateUsing(fn ($state) => $state ? $state->format('Y-m-d H:i') : __('Never')),

            ExportColumn::make('created_at')
                ->label(__('Created At'))
                ->formatStateUsing(fn ($state) => $state->format('Y-m-d H:i')),

            ExportColumn::make('updated_at')
                ->label(__('Updated At'))
                ->formatStateUsing(fn ($state) => $state->format('Y-m-d H:i')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your user export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }

    public function getJobConnection(): ?string
    {
        return 'sync';
    }

    public function getXlsxWriterOptions(): ?Options
    {
        $options = new Options;

        // Set column widths for better readability
        $options->setColumnWidth(8, 1);   // ID column
        $options->setColumnWidth(25, 2);  // Full Name column
        $options->setColumnWidth(15, 3);  // Employment ID column
        $options->setColumnWidth(25, 4);  // Email column
        $options->setColumnWidth(15, 5);  // Phone column
        $options->setColumnWidth(15, 6);  // Mobile column
        $options->setColumnWidth(15, 7);  // Skype ID column

        $options->setColumnWidth(15, 12); // User Type column
        $options->setColumnWidth(10, 13); // Locale column
        $options->setColumnWidth(12, 14); // Language column
        $options->setColumnWidth(12, 15); // Direction column
        $options->setColumnWidth(10, 16); // Active column

        $options->setColumnWidth(20, 19); // Email Verified column
        $options->setColumnWidth(20, 20); // Last Activity column
        $options->setColumnWidth(20, 21); // Created At column
        $options->setColumnWidth(20, 22); // Updated At column

        return $options;
    }

    public function configureXlsxWriterBeforeClose(Writer $writer): Writer
    {
        // Configure sheet view with frozen header row
        $sheetView = new SheetView;
        $sheetView->setFreezeRow(1); // Freeze the header row

        $sheet = $writer->getCurrentSheet();
        $sheet->setSheetView($sheetView);
        $sheet->setName('Users Export');

        return $writer;
    }

    /**
     * Override makeXlsxHeaderRow to create header cells with special styling
     */
    public function makeXlsxHeaderRow(array $values, ?Style $style = null): Row
    {
        $columnStyles = [];

        // Apply special header styling to all columns
        foreach ($values as $index => $value) {
            $columnStyles[$index] = $this->getXlsxHeaderCellStyle();
        }

        // Create row with individual column styles
        return Row::fromValuesWithStyles($values, null, $columnStyles);
    }

    /**
     * Override makeXlsxRow to create individual cells with column-specific styling
     */
    public function makeXlsxRow(array $values, ?Style $style = null): Row
    {
        $columnStyles = [];

        foreach ($values as $index => $value) {
            // Get column-specific style for each cell
            $columnStyles[$index] = $this->getXlsxColumnStyle($index, $value);
        }

        // Create row with individual column styles
        return Row::fromValuesWithStyles($values, null, $columnStyles);
    }

    public function getXlsxRowStyle(): ?Style
    {
        // Return a base style for data rows (background will be set per column)
        return (new Style)
            ->setFontSize(10);
    }

    public function getXlsxCellStyle(): ?Style
    {
        // Return a professional default style for all cells
        return (new Style)
            ->setFontSize(10)
            ->setCellAlignment(CellAlignment::LEFT);
    }

    public function getXlsxHeaderRowStyle(): ?Style
    {
        // Professional header styling with enhanced colors
        return (new Style)
            ->setFontBold()
            ->setFontSize(12)
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor(Color::rgb(25, 25, 112)) // Midnight blue
            ->setCellAlignment(CellAlignment::CENTER);
    }

    public function getXlsxHeaderCellStyle(): ?Style
    {
        // Individual header cell styling with enhanced colors
        return (new Style)
            ->setFontBold()
            ->setFontSize(12)
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor(Color::rgb(25, 25, 112)) // Midnight blue
            ->setCellAlignment(CellAlignment::CENTER);
    }

    /**
     * Get conditional styling for specific columns based on their content
     * Organized by column groups with distinct background colors
     */
    public function getXlsxColumnStyle(int $columnIndex, mixed $value): ?Style
    {
        $baseStyle = (new Style)
            ->setFontSize(10);

        return match ($columnIndex) {
            // GROUP 1: Basic Information (ID, Name, Employment ID) - Dark Blue Theme
            0 => $baseStyle // ID column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setFontBold()
                ->setBackgroundColor(Color::rgb(173, 216, 230)) // Steel blue
                ->setFontColor(Color::rgb(255, 255, 255)), // White text for better contrast
            1 => $baseStyle // Full Name column
                ->setFontBold()
                ->setFontColor(Color::rgb(255, 255, 255)) // White text for better contrast
                ->setBackgroundColor(Color::rgb(173, 216, 230)) // Steel blue
                ->setCellAlignment(CellAlignment::LEFT),
            2 => $baseStyle // Employment ID column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setFontBold()
                ->setBackgroundColor(Color::rgb(173, 216, 230)) // Steel blue
                ->setFontColor(Color::rgb(255, 255, 255)), // White text for better contrast

            // GROUP 2: Contact Information (Email, Phone, Mobile, Skype) - Dark Green Theme
            3 => $baseStyle // Email column
                ->setCellAlignment(CellAlignment::LEFT)
                ->setBackgroundColor(Color::rgb(144, 238, 144)) // Light green
                ->setFontColor(Color::rgb(255, 255, 255)), // White text for better contrast
            4, 5, 6 => $baseStyle // Phone, Mobile, Skype columns
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(144, 238, 144)) // Light green
                ->setFontColor(Color::rgb(255, 255, 255)), // White text for better contrast

            // GROUP 4: User Settings (User Type, Locale, Language, Direction) - Dark Purple Theme
            11, 12, 13, 14 => $baseStyle // Settings columns
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(186, 85, 211)) // Medium orchid
                ->setFontColor(Color::rgb(255, 255, 255)), // White text for better contrast

            // GROUP 5: Status Indicators (Active, Department Head, HQ User) - Dark Red Theme
            15 => $baseStyle // Active column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setFontBold()
                ->setBackgroundColor($this->getActiveColor($value))
                ->setFontColor(Color::WHITE), // White text for better contrast

            // GROUP 6: Timestamps (Email Verified, Last Activity, Created At, Updated At) - Dark Gray Theme
            18, 19, 20, 21 => $baseStyle // Date columns
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(169, 169, 169)) // Dark gray
                ->setFontColor(Color::rgb(255, 255, 255)), // White text for better contrast
            default => $baseStyle,
        };
    }

    private function getActiveColor(string $active): string
    {
        return $active === __('Yes')
            ? Color::rgb(0, 128, 0) // Forest green
            : Color::rgb(220, 20, 60); // Crimson
    }
}
