<?php

namespace App\Filament\Exports;

use App\Models\FAQ;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;

class FAQExporter extends Exporter
{
    protected static ?string $model = FAQ::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label(__('ID'))
                ->formatStateUsing(fn ($state) => '#'.$state),

            ExportColumn::make('question')
                ->label(__('Question'))
                ->state(function (FAQ $record): string {
                    return $record->getTranslation('question', app()->getLocale()) ?? __('No question');
                }),

            ExportColumn::make('answer')
                ->label(__('Answer'))
                ->state(function (FAQ $record): string {
                    return $record->getTranslation('answer', app()->getLocale()) ?? __('No answer');
                }),

            ExportColumn::make('category')
                ->label(__('Category'))
                ->formatStateUsing(fn ($state) => $state ?: __('Uncategorized')),

            ExportColumn::make('is_active')
                ->label(__('Status'))
                ->state(function (FAQ $record): string {
                    return $record->is_active ? __('Active') : __('Inactive');
                }),

            ExportColumn::make('sort_order')
                ->label(__('Sort Order'))
                ->formatStateUsing(fn ($state) => $state ?: '0'),

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
        $body = 'Your FAQ export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

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

        $options->setColumnWidth(8, 1);   // ID column
        $options->setColumnWidth(50, 2);  // Question column
        $options->setColumnWidth(60, 3);  // Answer column
        $options->setColumnWidth(20, 4);  // Category column
        $options->setColumnWidth(12, 5);  // Status column
        $options->setColumnWidth(12, 6);  // Sort Order column
        $options->setColumnWidth(20, 7);  // Created At column
        $options->setColumnWidth(20, 8);  // Updated At column

        return $options;
    }

    public function configureXlsxWriterBeforeClose(Writer $writer): Writer
    {
        $sheetView = new SheetView;
        $sheetView->setFreezeRow(1);

        $sheet = $writer->getCurrentSheet();
        $sheet->setSheetView($sheetView);
        $sheet->setName('FAQs Export');

        return $writer;
    }

    public function makeXlsxHeaderRow(array $values, ?Style $style = null): Row
    {
        $columnStyles = [];

        foreach ($values as $index => $value) {
            $columnStyles[$index] = $this->getXlsxHeaderCellStyle();
        }

        return Row::fromValuesWithStyles($values, null, $columnStyles);
    }

    public function makeXlsxRow(array $values, ?Style $style = null): Row
    {
        $columnStyles = [];

        foreach ($values as $index => $value) {
            $columnStyles[$index] = $this->getXlsxColumnStyle($index, $value);
        }

        return Row::fromValuesWithStyles($values, null, $columnStyles);
    }

    public function getXlsxRowStyle(): ?Style
    {
        return (new Style)
            ->setFontSize(10);
    }

    public function getXlsxCellStyle(): ?Style
    {
        return (new Style)
            ->setFontSize(10)
            ->setCellAlignment(CellAlignment::LEFT);
    }

    public function getXlsxHeaderRowStyle(): ?Style
    {
        return (new Style)
            ->setFontBold()
            ->setFontSize(12)
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor(Color::rgb(25, 25, 112))
            ->setCellAlignment(CellAlignment::CENTER);
    }

    public function getXlsxHeaderCellStyle(): ?Style
    {
        return (new Style)
            ->setFontBold()
            ->setFontSize(12)
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor(Color::rgb(25, 25, 112))
            ->setCellAlignment(CellAlignment::CENTER);
    }

    public function getXlsxColumnStyle(int $columnIndex, mixed $value): ?Style
    {
        $baseStyle = (new Style)
            ->setFontSize(10);

        return match ($columnIndex) {
            0 => $baseStyle // ID column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setFontBold()
                ->setBackgroundColor(Color::rgb(173, 216, 230))
                ->setFontColor(Color::rgb(0, 0, 139)),
            1, 2 => $baseStyle // Question, Answer columns
                ->setCellAlignment(CellAlignment::LEFT)
                ->setFontBold()
                ->setBackgroundColor(Color::rgb(173, 216, 230))
                ->setFontColor(Color::rgb(0, 0, 139)),
            3 => $baseStyle // Category column
                ->setCellAlignment(CellAlignment::LEFT)
                ->setBackgroundColor(Color::rgb(144, 238, 144))
                ->setFontColor(Color::rgb(0, 100, 0)),
            4 => $baseStyle // Status column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setFontBold()
                ->setBackgroundColor($this->getStatusColor($value))
                ->setFontColor(Color::WHITE),
            5 => $baseStyle // Sort Order column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(169, 169, 169))
                ->setFontColor(Color::rgb(47, 79, 79)),
            6, 7 => $baseStyle // Date columns
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(169, 169, 169))
                ->setFontColor(Color::rgb(47, 79, 79)),
            default => $baseStyle,
        };
    }

    private function getStatusColor(string $status): string
    {
        return $status === __('Active')
            ? Color::rgb(0, 128, 0)
            : Color::rgb(220, 20, 60);
    }
}
