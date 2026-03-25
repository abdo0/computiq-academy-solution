<?php

namespace App\Filament\Exports;

use App\Models\Organization;
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

class OrganizationExporter extends Exporter
{
    protected static ?string $model = Organization::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label(__('ID'))
                ->formatStateUsing(fn ($state) => '#'.$state),

            ExportColumn::make('name')
                ->label(__('Name'))
                ->formatStateUsing(fn ($state) => $state ?: __('No name')),

            ExportColumn::make('code')
                ->label(__('Code'))
                ->formatStateUsing(fn ($state) => $state ?: __('No code')),

            ExportColumn::make('phone')
                ->label(__('Phone'))
                ->formatStateUsing(fn ($state) => $state ?: __('No phone')),

            ExportColumn::make('country_code')
                ->label(__('Country Code'))
                ->formatStateUsing(fn ($state) => $state ?: __('Not set')),

            ExportColumn::make('email')
                ->label(__('Email'))
                ->formatStateUsing(fn ($state) => $state ?: __('No email')),

            ExportColumn::make('address')
                ->label(__('Address'))
                ->formatStateUsing(fn ($state) => $state ?: __('No address')),

            ExportColumn::make('verification_status')
                ->label(__('Verification Status'))
                ->state(function (Organization $record): string {
                    return $record->verification_status?->getLabel() ?? __('Unknown');
                }),

            ExportColumn::make('verification_tier')
                ->label(__('Verification Tier'))
                ->state(function (Organization $record): string {
                    return $record->verification_tier?->getLabel() ?? __('Basic');
                }),

            ExportColumn::make('status')
                ->label(__('Status'))
                ->state(function (Organization $record): string {
                    return $record->status?->getLabel() ?? __('Unknown');
                }),

            ExportColumn::make('verification_submitted_at')
                ->label(__('Verification Submitted At'))
                ->formatStateUsing(fn ($state) => $state ? $state->format('Y-m-d H:i') : __('Not submitted')),

            ExportColumn::make('verification_reviewed_at')
                ->label(__('Verification Reviewed At'))
                ->formatStateUsing(fn ($state) => $state ? $state->format('Y-m-d H:i') : __('Not reviewed')),

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
        $body = 'Your organization export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

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
        $options->setColumnWidth(30, 2);  // Name column
        $options->setColumnWidth(15, 3);  // Code column
        $options->setColumnWidth(15, 4);  // Phone column
        $options->setColumnWidth(12, 5);  // Country Code column
        $options->setColumnWidth(25, 6);  // Email column
        $options->setColumnWidth(40, 7);  // Address column
        $options->setColumnWidth(20, 8);  // Verification Status column
        $options->setColumnWidth(18, 9);  // Verification Tier column
        $options->setColumnWidth(15, 10); // Status column
        $options->setColumnWidth(20, 11); // Verification Submitted At column
        $options->setColumnWidth(20, 12); // Verification Reviewed At column
        $options->setColumnWidth(12, 13); // Sort Order column
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
        $sheet->setName('Organizations Export');

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
            1, 2 => $baseStyle // Name, Code columns
                ->setFontBold()
                ->setFontColor(Color::rgb(0, 0, 139))
                ->setBackgroundColor(Color::rgb(173, 216, 230))
                ->setCellAlignment(CellAlignment::LEFT),
            3, 4, 5, 6 => $baseStyle // Phone, Country Code, Email, Address columns
                ->setCellAlignment(CellAlignment::LEFT)
                ->setBackgroundColor(Color::rgb(144, 238, 144))
                ->setFontColor(Color::rgb(0, 100, 0)),
            7, 8, 9 => $baseStyle // Verification Status, Tier, Status columns
                ->setCellAlignment(CellAlignment::CENTER)
                ->setFontBold()
                ->setBackgroundColor($this->getStatusColor($value))
                ->setFontColor(Color::WHITE),
            10, 11 => $baseStyle // Verification date columns
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(186, 85, 211))
                ->setFontColor(Color::rgb(75, 0, 130)),
            12 => $baseStyle // Sort Order column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(169, 169, 169))
                ->setFontColor(Color::rgb(47, 79, 79)),
            13, 14 => $baseStyle // Date columns
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(169, 169, 169))
                ->setFontColor(Color::rgb(47, 79, 79)),
            default => $baseStyle,
        };
    }

    private function getStatusColor(string $status): string
    {
        return match (strtolower($status)) {
            'verified', 'approved', 'active' => Color::rgb(0, 128, 0),
            'pending', 'under_review' => Color::rgb(255, 140, 0),
            'rejected', 'unverified', 'suspended', 'inactive' => Color::rgb(220, 20, 60),
            default => Color::rgb(105, 105, 105),
        };
    }
}
