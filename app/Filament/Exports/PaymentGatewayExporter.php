<?php

namespace App\Filament\Exports;

use App\Models\PaymentGateway;
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

class PaymentGatewayExporter extends Exporter
{
    protected static ?string $model = PaymentGateway::class;

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
                ->state(function (PaymentGateway $record): string {
                    return $record->getTranslation('name', app()->getLocale()) ?? __('No name');
                }),

            ExportColumn::make('type')
                ->label(__('Type'))
                ->state(function (PaymentGateway $record): string {
                    return $record->type?->getLabel() ?? __('Unknown');
                }),

            ExportColumn::make('description')
                ->label(__('Description'))
                ->state(function (PaymentGateway $record): string {
                    return $record->getTranslation('description', app()->getLocale()) ?? __('No description');
                }),

            ExportColumn::make('processing_fee')
                ->label(__('Processing Fee'))
                ->state(function (PaymentGateway $record): string {
                    return $record->processing_fee;
                }),

            ExportColumn::make('processing_fee_percentage')
                ->label(__('Fee Percentage'))
                ->formatStateUsing(fn ($state) => $state ? number_format($state, 2).'%' : __('N/A')),

            ExportColumn::make('processing_fee_fixed')
                ->label(__('Fixed Fee'))
                ->formatStateUsing(fn ($state) => $state ? number_format($state, 0).' '.(settings('currency', 'USD')) : __('N/A')),

            ExportColumn::make('is_active')
                ->label(__('Status'))
                ->state(function (PaymentGateway $record): string {
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
        $body = 'Your payment gateway export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

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
        $options->setColumnWidth(15, 2);  // Code column
        $options->setColumnWidth(25, 3);  // Name column
        $options->setColumnWidth(15, 4);  // Type column
        $options->setColumnWidth(40, 5);  // Description column
        $options->setColumnWidth(20, 6);  // Processing Fee column
        $options->setColumnWidth(15, 7);  // Fee Percentage column
        $options->setColumnWidth(15, 8);  // Fixed Fee column
        $options->setColumnWidth(12, 9);  // Status column
        $options->setColumnWidth(12, 10); // Sort Order column
        $options->setColumnWidth(20, 11); // Created At column
        $options->setColumnWidth(20, 12); // Updated At column

        return $options;
    }

    public function configureXlsxWriterBeforeClose(Writer $writer): Writer
    {
        $sheetView = new SheetView;
        $sheetView->setFreezeRow(1);

        $sheet = $writer->getCurrentSheet();
        $sheet->setSheetView($sheetView);
        $sheet->setName('Payment Gateways Export');

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
            3 => $baseStyle // Type column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(144, 238, 144))
                ->setFontColor(Color::rgb(0, 100, 0)),
            4 => $baseStyle // Description column
                ->setCellAlignment(CellAlignment::LEFT)
                ->setBackgroundColor(Color::rgb(255, 165, 0))
                ->setFontColor(Color::rgb(139, 69, 19)),
            5, 6, 7 => $baseStyle // Fee columns
                ->setCellAlignment(CellAlignment::RIGHT)
                ->setFontBold()
                ->setBackgroundColor(Color::rgb(186, 85, 211))
                ->setFontColor(Color::rgb(75, 0, 130)),
            8 => $baseStyle // Status column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setFontBold()
                ->setBackgroundColor($this->getStatusColor($value))
                ->setFontColor(Color::WHITE),
            9 => $baseStyle // Sort Order column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(169, 169, 169))
                ->setFontColor(Color::rgb(47, 79, 79)),
            10, 11 => $baseStyle // Date columns
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
