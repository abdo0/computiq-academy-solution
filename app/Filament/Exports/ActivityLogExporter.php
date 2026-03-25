<?php

namespace App\Filament\Exports;

use App\Models\ActivityLog;
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

class ActivityLogExporter extends Exporter
{
    protected static ?string $model = ActivityLog::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label(__('ID'))
                ->formatStateUsing(fn ($state) => '#'.$state),

            ExportColumn::make('rendered_message')
                ->label(__('Activity'))
                ->formatStateUsing(fn ($state) => $state ?: __('Unknown Activity')),

            ExportColumn::make('user_name')
                ->label(__('User'))
                ->state(function (ActivityLog $record): string {
                    return $record->user?->name ?? __('System');
                }),

            ExportColumn::make('branch_name')
                ->label(__('Branch'))
                ->state(function (ActivityLog $record): string {
                    return $record->branch?->name ?? __('N/A');
                }),

            ExportColumn::make('action')
                ->label(__('Action'))
                ->state(function (ActivityLog $record): string {
                    return $record->action_label ?? __('Unknown');
                }),

            ExportColumn::make('model_type')
                ->label(__('Model'))
                ->formatStateUsing(fn ($state) => $state ? __(class_basename($state)) : __('Unknown')),

            ExportColumn::make('model_id')
                ->label(__('Record ID'))
                ->formatStateUsing(fn ($state) => $state ?: __('N/A')),

            ExportColumn::make('description')
                ->label(__('Description'))
                ->formatStateUsing(fn ($state) => $state ?: __('No description')),

            ExportColumn::make('ip_address')
                ->label(__('IP Address'))
                ->formatStateUsing(fn ($state) => $state ?: __('N/A')),

            ExportColumn::make('user_agent')
                ->label(__('User Agent'))
                ->formatStateUsing(fn ($state) => $state ?: __('N/A')),

            ExportColumn::make('created_at')
                ->label(__('Created At'))
                ->formatStateUsing(fn ($state) => $state->format('Y-m-d H:i:s')),

            ExportColumn::make('updated_at')
                ->label(__('Updated At'))
                ->formatStateUsing(fn ($state) => $state->format('Y-m-d H:i:s')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your activity log export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

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

        // Set column widths for better readability following excel-export rules
        $options->setColumnWidth(8, 1);   // ID column
        $options->setColumnWidth(40, 2); // Activity column
        $options->setColumnWidth(20, 3); // User column
        $options->setColumnWidth(20, 4); // Branch column
        $options->setColumnWidth(15, 5); // Action column
        $options->setColumnWidth(15, 6); // Model column
        $options->setColumnWidth(10, 7); // Record ID column
        $options->setColumnWidth(30, 8); // Description column
        $options->setColumnWidth(15, 9); // IP Address column
        $options->setColumnWidth(30, 10); // User Agent column
        $options->setColumnWidth(20, 11); // Created At column
        $options->setColumnWidth(20, 12); // Updated At column

        return $options;
    }

    public function configureXlsxWriterBeforeClose(Writer $writer): Writer
    {
        // Configure sheet view with frozen header row
        $sheetView = new SheetView;
        $sheetView->setFreezeRow(1); // Freeze the header row

        $sheet = $writer->getCurrentSheet();
        $sheet->setSheetView($sheetView);
        $sheet->setName('Activity Logs Export');

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
            // GROUP 1: Basic Information (ID, Activity) - Dark Blue Theme
            0 => $baseStyle // ID column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setFontBold()
                ->setBackgroundColor(Color::rgb(173, 216, 230)) // Steel blue
                ->setFontColor(Color::rgb(0, 0, 139)), // Dark blue text
            1 => $baseStyle // Activity column
                ->setFontBold()
                ->setFontColor(Color::rgb(0, 0, 139)) // Dark blue text
                ->setBackgroundColor(Color::rgb(173, 216, 230)) // Steel blue
                ->setCellAlignment(CellAlignment::LEFT),

            // GROUP 2: User Context (User, Branch) - Dark Green Theme
            2 => $baseStyle // User column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(144, 238, 144)) // Light green
                ->setFontColor(Color::rgb(0, 100, 0)), // Dark green text
            3 => $baseStyle // Branch column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(144, 238, 144)) // Light green
                ->setFontColor(Color::rgb(0, 100, 0)), // Dark green text

            // GROUP 3: Action Details (Action, Model, Record ID) - Dark Orange Theme
            4 => $baseStyle // Action column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(255, 165, 0)) // Orange
                ->setFontColor(Color::rgb(139, 69, 19)), // Saddle brown text
            5 => $baseStyle // Model column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(255, 165, 0)) // Orange
                ->setFontColor(Color::rgb(139, 69, 19)), // Saddle brown text
            6 => $baseStyle // Record ID column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setFontBold()
                ->setBackgroundColor(Color::rgb(255, 165, 0)) // Orange
                ->setFontColor(Color::rgb(139, 69, 19)), // Saddle brown text

            // GROUP 4: Technical Details (Description, IP Address, User Agent) - Dark Purple Theme
            7 => $baseStyle // Description column
                ->setCellAlignment(CellAlignment::LEFT)
                ->setBackgroundColor(Color::rgb(186, 85, 211)) // Medium orchid
                ->setFontColor(Color::rgb(75, 0, 130)), // Indigo text
            8 => $baseStyle // IP Address column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(186, 85, 211)) // Medium orchid
                ->setFontColor(Color::rgb(75, 0, 130)), // Indigo text
            9 => $baseStyle // User Agent column
                ->setCellAlignment(CellAlignment::LEFT)
                ->setBackgroundColor(Color::rgb(186, 85, 211)) // Medium orchid
                ->setFontColor(Color::rgb(75, 0, 130)), // Indigo text

            // GROUP 5: Timestamps (Created At, Updated At) - Dark Gray Theme
            10, 11 => $baseStyle // Date columns
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(169, 169, 169)) // Dark gray
                ->setFontColor(Color::rgb(47, 79, 79)), // Dark slate gray text
            default => $baseStyle,
        };
    }
}
