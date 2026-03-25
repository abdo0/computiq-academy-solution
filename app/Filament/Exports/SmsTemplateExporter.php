<?php

namespace App\Filament\Exports;

use App\Models\SmsTemplate;
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

class SmsTemplateExporter extends Exporter
{
    protected static ?string $model = SmsTemplate::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label(__('ID'))
                ->formatStateUsing(fn ($state) => '#'.$state),

            ExportColumn::make('code')
                ->label(__('Code'))
                ->formatStateUsing(fn ($state) => $state ?: __('No code')),

            ExportColumn::make('name')
                ->label(__('Name'))
                ->state(function (SmsTemplate $record): string {
                    return $record->getTranslation('name', app()->getLocale()) ?? __('Untitled Template');
                }),

            ExportColumn::make('purpose')
                ->label(__('Purpose'))
                ->state(function (SmsTemplate $record): string {
                    return $record->purpose?->getLabel() ?? __('Unknown');
                }),

            ExportColumn::make('content')
                ->label(__('Content'))
                ->state(function (SmsTemplate $record): string {
                    return $record->getTranslation('content', app()->getLocale()) ?? __('No content');
                }),

            ExportColumn::make('is_default')
                ->label(__('Default'))
                ->state(function (SmsTemplate $record): string {
                    return $record->is_default ? __('Yes') : __('No');
                }),

            ExportColumn::make('is_active')
                ->label(__('Active'))
                ->state(function (SmsTemplate $record): string {
                    return $record->is_active ? __('Yes') : __('No');
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
        $body = __('Your SMS template export has completed and :count :rows exported.', [
            'count' => Number::format($export->successful_rows),
            'rows' => str('row')->plural($export->successful_rows),
        ]);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' '.__('failed to export.');
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
        $options->setColumnWidth(20, 2);  // Code column
        $options->setColumnWidth(30, 3);  // Name column
        $options->setColumnWidth(25, 4);  // Purpose column
        $options->setColumnWidth(50, 5);  // Content column
        $options->setColumnWidth(12, 6);  // Default column
        $options->setColumnWidth(12, 7);  // Active column
        $options->setColumnWidth(12, 8);  // Sort Order column
        $options->setColumnWidth(20, 9);  // Created At column
        $options->setColumnWidth(20, 10); // Updated At column

        return $options;
    }

    public function configureXlsxWriterBeforeClose(Writer $writer): Writer
    {
        $sheetView = new SheetView;
        $sheetView->setFreezeRow(1);

        $sheet = $writer->getCurrentSheet();
        $sheet->setSheetView($sheetView);
        $sheet->setName(__('SMS Templates Export'));

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
            1, 2 => $baseStyle // Code, Name columns
                ->setFontBold()
                ->setFontColor(Color::rgb(0, 0, 139))
                ->setBackgroundColor(Color::rgb(173, 216, 230))
                ->setCellAlignment(CellAlignment::LEFT),
            3 => $baseStyle // Purpose column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(144, 238, 144))
                ->setFontColor(Color::rgb(0, 100, 0)),
            4 => $baseStyle // Content column
                ->setCellAlignment(CellAlignment::LEFT)
                ->setBackgroundColor(Color::rgb(255, 165, 0))
                ->setFontColor(Color::rgb(139, 69, 19)),
            5, 6 => $baseStyle // Default, Active columns
                ->setCellAlignment(CellAlignment::CENTER)
                ->setFontBold()
                ->setBackgroundColor($this->getStatusColor($value))
                ->setFontColor(Color::WHITE),
            7 => $baseStyle // Sort Order column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(169, 169, 169))
                ->setFontColor(Color::rgb(47, 79, 79)),
            8, 9 => $baseStyle // Date columns
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(169, 169, 169))
                ->setFontColor(Color::rgb(47, 79, 79)),
            default => $baseStyle,
        };
    }

    private function getStatusColor(string $status): string
    {
        return $status === __('Yes')
            ? Color::rgb(0, 128, 0)
            : Color::rgb(220, 20, 60);
    }
}
