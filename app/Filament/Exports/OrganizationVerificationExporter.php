<?php

namespace App\Filament\Exports;

use App\Models\OrganizationVerification;
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

class OrganizationVerificationExporter extends Exporter
{
    protected static ?string $model = OrganizationVerification::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label(__('ID'))
                ->formatStateUsing(fn ($state) => '#'.$state),

            ExportColumn::make('organization.name')
                ->label(__('Organization'))
                ->state(function (OrganizationVerification $record): string {
                    return $record->organization?->name ?? __('No organization');
                }),

            ExportColumn::make('status')
                ->label(__('Status'))
                ->state(function (OrganizationVerification $record): string {
                    return $record->status?->getLabel() ?? __('Unknown');
                }),

            ExportColumn::make('tier')
                ->label(__('Tier'))
                ->state(function (OrganizationVerification $record): string {
                    return $record->tier?->getLabel() ?? __('Basic');
                }),

            ExportColumn::make('registration_number')
                ->label(__('Registration Number'))
                ->formatStateUsing(fn ($state) => $state ?: __('Not provided')),

            ExportColumn::make('tax_id')
                ->label(__('Tax ID'))
                ->formatStateUsing(fn ($state) => $state ?: __('Not provided')),

            ExportColumn::make('contact_person_name')
                ->label(__('Contact Person'))
                ->formatStateUsing(fn ($state) => $state ?: __('Not provided')),

            ExportColumn::make('contact_person_email')
                ->label(__('Contact Email'))
                ->formatStateUsing(fn ($state) => $state ?: __('Not provided')),

            ExportColumn::make('contact_person_phone')
                ->label(__('Contact Phone'))
                ->formatStateUsing(fn ($state) => $state ?: __('Not provided')),

            ExportColumn::make('country.name')
                ->label(__('Country'))
                ->state(function (OrganizationVerification $record): string {
                    return $record->country?->name ?? __('Not specified');
                }),

            ExportColumn::make('city')
                ->label(__('City'))
                ->formatStateUsing(fn ($state) => $state ?: __('Not specified')),

            ExportColumn::make('submitted_at')
                ->label(__('Submitted At'))
                ->formatStateUsing(fn ($state) => $state ? $state->format('Y-m-d H:i') : __('Not submitted')),

            ExportColumn::make('reviewed_at')
                ->label(__('Reviewed At'))
                ->formatStateUsing(fn ($state) => $state ? $state->format('Y-m-d H:i') : __('Not reviewed')),

            ExportColumn::make('reviewer.name')
                ->label(__('Reviewed By'))
                ->state(function (OrganizationVerification $record): string {
                    return $record->reviewer?->name ?? __('Not reviewed');
                }),

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
        $body = 'Your organization verification export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

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
        $options->setColumnWidth(30, 2);  // Organization column
        $options->setColumnWidth(18, 3);  // Status column
        $options->setColumnWidth(15, 4);  // Tier column
        $options->setColumnWidth(20, 5);  // Registration Number column
        $options->setColumnWidth(18, 6);  // Tax ID column
        $options->setColumnWidth(25, 7);  // Contact Person column
        $options->setColumnWidth(25, 8);  // Contact Email column
        $options->setColumnWidth(18, 9);  // Contact Phone column
        $options->setColumnWidth(20, 10); // Country column
        $options->setColumnWidth(20, 11); // City column
        $options->setColumnWidth(20, 12); // Submitted At column
        $options->setColumnWidth(20, 13); // Reviewed At column
        $options->setColumnWidth(20, 14); // Reviewed By column
        $options->setColumnWidth(20, 15); // Created At column
        $options->setColumnWidth(20, 16); // Updated At column

        return $options;
    }

    public function configureXlsxWriterBeforeClose(Writer $writer): Writer
    {
        $sheetView = new SheetView;
        $sheetView->setFreezeRow(1);

        $sheet = $writer->getCurrentSheet();
        $sheet->setSheetView($sheetView);
        $sheet->setName('Organization Verifications Export');

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
            1 => $baseStyle // Organization column
                ->setCellAlignment(CellAlignment::LEFT)
                ->setFontBold()
                ->setBackgroundColor(Color::rgb(144, 238, 144))
                ->setFontColor(Color::rgb(0, 100, 0)),
            2, 3 => $baseStyle // Status, Tier columns
                ->setCellAlignment(CellAlignment::CENTER)
                ->setFontBold()
                ->setBackgroundColor($this->getStatusColor($value))
                ->setFontColor(Color::WHITE),
            4, 5, 6, 7, 8 => $baseStyle // Registration, Tax, Contact columns
                ->setCellAlignment(CellAlignment::LEFT)
                ->setBackgroundColor(Color::rgb(255, 165, 0))
                ->setFontColor(Color::rgb(139, 69, 19)),
            9, 10 => $baseStyle // Country, City columns
                ->setCellAlignment(CellAlignment::LEFT)
                ->setBackgroundColor(Color::rgb(186, 85, 211))
                ->setFontColor(Color::rgb(75, 0, 130)),
            11, 12, 13 => $baseStyle // Review columns
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(186, 85, 211))
                ->setFontColor(Color::rgb(75, 0, 130)),
            14, 15 => $baseStyle // Date columns
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(169, 169, 169))
                ->setFontColor(Color::rgb(47, 79, 79)),
            default => $baseStyle,
        };
    }

    private function getStatusColor(string $status): string
    {
        return match (strtolower($status)) {
            'verified', 'approved' => Color::rgb(0, 128, 0),
            'pending', 'under_review' => Color::rgb(255, 140, 0),
            'rejected', 'unverified' => Color::rgb(220, 20, 60),
            default => Color::rgb(105, 105, 105),
        };
    }
}
