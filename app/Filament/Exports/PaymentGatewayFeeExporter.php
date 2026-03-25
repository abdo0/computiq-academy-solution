<?php

namespace App\Filament\Exports;

use App\Models\PaymentGatewayFee;
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

class PaymentGatewayFeeExporter extends Exporter
{
    protected static ?string $model = PaymentGatewayFee::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label(__('ID'))
                ->formatStateUsing(fn ($state) => '#'.$state),

            ExportColumn::make('fee_type')
                ->label(__('Fee Type'))
                ->state(function (PaymentGatewayFee $record): string {
                    return $record->fee_type?->getLabel() ?? __('Unknown');
                }),

            ExportColumn::make('paymentGateway.code')
                ->label(__('Payment Gateway'))
                ->state(function (PaymentGatewayFee $record): string {
                    return $record->paymentGateway?->code ?? __('No gateway');
                }),

            ExportColumn::make('organization_type')
                ->label(__('Organization Type'))
                ->state(function (PaymentGatewayFee $record): string {
                    return $record->organization_type?->getLabel() ?? __('Not specified');
                }),

            ExportColumn::make('campaign_type')
                ->label(__('Campaign Type'))
                ->state(function (PaymentGatewayFee $record): string {
                    return $record->campaign_type?->getLabel() ?? __('Not specified');
                }),

            ExportColumn::make('settlement_method')
                ->label(__('Settlement Method'))
                ->state(function (PaymentGatewayFee $record): string {
                    return $record->settlement_method?->getLabel() ?? __('Not specified');
                }),

            ExportColumn::make('fee_amount')
                ->label(__('Fee Amount'))
                ->state(function (PaymentGatewayFee $record): string {
                    return $record->fee_amount;
                }),

            ExportColumn::make('description')
                ->label(__('Description'))
                ->state(function (PaymentGatewayFee $record): string {
                    return $record->getTranslation('description', app()->getLocale()) ?? __('No description');
                }),

            ExportColumn::make('is_active')
                ->label(__('Status'))
                ->state(function (PaymentGatewayFee $record): string {
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
        $body = 'Your payment gateway fee export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

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
        $options->setColumnWidth(20, 2);  // Fee Type column
        $options->setColumnWidth(20, 3);  // Payment Gateway column
        $options->setColumnWidth(20, 4);  // Organization Type column
        $options->setColumnWidth(20, 5);  // Campaign Type column
        $options->setColumnWidth(20, 6);  // Settlement Method column
        $options->setColumnWidth(25, 7);  // Fee Amount column
        $options->setColumnWidth(40, 8);  // Description column
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
        $sheet->setName('Payment Gateway Fees Export');

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
            1, 2, 3, 4, 5 => $baseStyle // Type columns
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(144, 238, 144))
                ->setFontColor(Color::rgb(0, 100, 0)),
            6 => $baseStyle // Fee Amount column
                ->setCellAlignment(CellAlignment::RIGHT)
                ->setFontBold()
                ->setBackgroundColor(Color::rgb(186, 85, 211))
                ->setFontColor(Color::rgb(75, 0, 130)),
            7 => $baseStyle // Description column
                ->setCellAlignment(CellAlignment::LEFT)
                ->setBackgroundColor(Color::rgb(255, 165, 0))
                ->setFontColor(Color::rgb(139, 69, 19)),
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
