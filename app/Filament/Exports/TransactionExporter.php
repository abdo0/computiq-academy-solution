<?php

namespace App\Filament\Exports;

use App\Models\Transaction;
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

class TransactionExporter extends Exporter
{
    protected static ?string $model = Transaction::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label(__('ID'))
                ->formatStateUsing(fn ($state) => '#'.$state),

            ExportColumn::make('transaction_ref')
                ->label(__('Transaction Ref'))
                ->formatStateUsing(fn ($state) => $state ?: __('No reference')),

            ExportColumn::make('type')
                ->label(__('Type'))
                ->state(function (Transaction $record): string {
                    return $record->type?->getLabel() ?? __('Unknown');
                }),

            ExportColumn::make('donation.campaign.name')
                ->label(__('Campaign'))
                ->state(function (Transaction $record): string {
                    return $record->donation?->campaign?->name ?? __('N/A');
                }),

            ExportColumn::make('donation.donor.name')
                ->label(__('Donor'))
                ->state(function (Transaction $record): string {
                    if ($record->isDonation()) {
                        return $record->donation?->donor?->name ?? __('Anonymous');
                    }
                    if ($record->isWalletTopup()) {
                        return $record->donorWallet?->donor?->name ?? __('Unknown');
                    }

                    return __('N/A');
                }),

            ExportColumn::make('paymentGateway.name')
                ->label(__('Payment Gateway'))
                ->state(function (Transaction $record): string {
                    return $record->paymentGateway?->getTranslation('name', app()->getLocale()) ?? __('No gateway');
                }),

            ExportColumn::make('paymentMethod.name')
                ->label(__('Payment Method'))
                ->state(function (Transaction $record): string {
                    return $record->paymentMethod?->name ?? __('Not specified');
                }),

            ExportColumn::make('amount')
                ->label(__('Amount'))
                ->formatStateUsing(fn ($state) => $state ? number_format($state, 2).' '.(settings('currency', 'USD')) : __('No amount')),

            ExportColumn::make('total_amount')
                ->label(__('Total Paid'))
                ->formatStateUsing(fn ($state) => $state ? number_format($state, 2).' '.(settings('currency', 'USD')) : __('N/A')),

            ExportColumn::make('net_amount')
                ->label(__('Net Amount'))
                ->formatStateUsing(fn ($state) => $state ? number_format($state, 2).' '.(settings('currency', 'USD')) : __('N/A')),

            ExportColumn::make('gateway_processing_fee')
                ->label(__('Gateway Fee'))
                ->formatStateUsing(fn ($state) => $state ? number_format($state, 2).' '.(settings('currency', 'USD')) : __('No fee')),

            ExportColumn::make('platform_commission')
                ->label(__('Platform Commission'))
                ->formatStateUsing(fn ($state) => $state ? number_format($state, 2).' '.(settings('currency', 'USD')) : __('No commission')),

            ExportColumn::make('status')
                ->label(__('Status'))
                ->state(function (Transaction $record): string {
                    return $record->status?->getLabel() ?? __('Unknown');
                }),

            ExportColumn::make('gateway_transaction_id')
                ->label(__('Gateway TXN ID'))
                ->formatStateUsing(fn ($state) => $state ?: __('N/A')),

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
        $body = 'Your transaction export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

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
        $options->setColumnWidth(20, 2);  // Transaction Ref column
        $options->setColumnWidth(15, 3);  // Type column
        $options->setColumnWidth(30, 4);  // Campaign column
        $options->setColumnWidth(25, 5);  // Donor column
        $options->setColumnWidth(20, 6);  // Payment Gateway column
        $options->setColumnWidth(15, 7);  // Amount column
        $options->setColumnWidth(15, 8);  // Total Paid column
        $options->setColumnWidth(15, 9);  // Net Amount column
        $options->setColumnWidth(15, 10); // Gateway Fee column
        $options->setColumnWidth(18, 11); // Platform Commission column
        $options->setColumnWidth(15, 12); // Status column
        $options->setColumnWidth(20, 13); // Gateway TXN ID column
        $options->setColumnWidth(20, 14); // Created At column
        $options->setColumnWidth(20, 15); // Updated At column

        return $options;
    }

    public function configureXlsxWriterBeforeClose(Writer $writer): Writer
    {
        $sheetView = new SheetView;
        $sheetView->setFreezeRow(1);

        $sheet = $writer->getCurrentSheet();
        $sheet->setSheetView($sheetView);
        $sheet->setName('Transactions Export');

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
            1 => $baseStyle // Transaction Ref column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setFontBold()
                ->setBackgroundColor(Color::rgb(173, 216, 230))
                ->setFontColor(Color::rgb(0, 0, 139)),
            2 => $baseStyle // Type column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setFontBold()
                ->setBackgroundColor(Color::rgb(144, 238, 144))
                ->setFontColor(Color::rgb(0, 100, 0)),
            3, 4, 5 => $baseStyle // Campaign, Donor, Payment Gateway columns
                ->setCellAlignment(CellAlignment::LEFT)
                ->setBackgroundColor(Color::rgb(144, 238, 144))
                ->setFontColor(Color::rgb(0, 100, 0)),
            6, 7, 8, 9, 10 => $baseStyle // Amount columns
                ->setCellAlignment(CellAlignment::RIGHT)
                ->setFontBold()
                ->setBackgroundColor(Color::rgb(255, 99, 71))
                ->setFontColor(Color::rgb(139, 0, 0)),
            11 => $baseStyle // Status column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setFontBold()
                ->setBackgroundColor($this->getStatusColor($value))
                ->setFontColor(Color::WHITE),
            12 => $baseStyle // Gateway TXN ID column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(186, 85, 211))
                ->setFontColor(Color::rgb(75, 0, 130)),
            13, 14, 15 => $baseStyle // Date columns
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(169, 169, 169))
                ->setFontColor(Color::rgb(47, 79, 79)),
            default => $baseStyle,
        };
    }

    private function getStatusColor(string $status): string
    {
        return match (strtolower($status)) {
            'completed', 'success', 'settled' => Color::rgb(0, 128, 0),
            'pending', 'processing' => Color::rgb(255, 140, 0),
            'failed', 'cancelled', 'rejected' => Color::rgb(220, 20, 60),
            default => Color::rgb(105, 105, 105),
        };
    }
}
