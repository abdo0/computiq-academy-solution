<?php

namespace App\Filament\Exports;

use App\Models\Article;
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

class ArticleExporter extends Exporter
{
    protected static ?string $model = Article::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label(__('ID'))
                ->formatStateUsing(fn ($state) => '#'.$state),

            ExportColumn::make('title')
                ->label(__('Title'))
                ->state(function (Article $record): string {
                    return $record->display_title ?? __('Untitled Article');
                }),

            ExportColumn::make('category.name')
                ->label(__('Category'))
                ->state(function (Article $record): string {
                    return $record->category?->getTranslation('name', app()->getLocale()) ?? __('Uncategorized');
                }),

            ExportColumn::make('author.name')
                ->label(__('Author'))
                ->state(function (Article $record): string {
                    return $record->author?->name ?? __('Unknown Author');
                }),

            ExportColumn::make('excerpt')
                ->label(__('Excerpt'))
                ->state(function (Article $record): string {
                    return $record->getTranslation('excerpt', app()->getLocale()) ?? __('No excerpt');
                }),

            ExportColumn::make('is_published')
                ->label(__('Published'))
                ->state(function (Article $record): string {
                    return $record->is_published ? __('Yes') : __('No');
                }),

            ExportColumn::make('published_at')
                ->label(__('Published At'))
                ->formatStateUsing(fn ($state) => $state ? $state->format('Y-m-d H:i') : __('Not published')),

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
        $body = 'Your article export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

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
        $options->setColumnWidth(40, 2);  // Title column
        $options->setColumnWidth(20, 3);  // Category column
        $options->setColumnWidth(20, 4);  // Author column
        $options->setColumnWidth(50, 5);  // Excerpt column
        $options->setColumnWidth(12, 6);  // Published column
        $options->setColumnWidth(20, 7);  // Published At column
        $options->setColumnWidth(12, 8);  // Sort Order column
        $options->setColumnWidth(20, 9);   // Created At column
        $options->setColumnWidth(20, 10); // Updated At column

        return $options;
    }

    public function configureXlsxWriterBeforeClose(Writer $writer): Writer
    {
        $sheetView = new SheetView;
        $sheetView->setFreezeRow(1);

        $sheet = $writer->getCurrentSheet();
        $sheet->setSheetView($sheetView);
        $sheet->setName('Articles Export');

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
            1 => $baseStyle // Title column
                ->setFontBold()
                ->setFontColor(Color::rgb(0, 0, 139))
                ->setBackgroundColor(Color::rgb(173, 216, 230))
                ->setCellAlignment(CellAlignment::LEFT),
            2, 3 => $baseStyle // Category, Author columns
                ->setCellAlignment(CellAlignment::LEFT)
                ->setBackgroundColor(Color::rgb(144, 238, 144))
                ->setFontColor(Color::rgb(0, 100, 0)),
            4 => $baseStyle // Excerpt column
                ->setCellAlignment(CellAlignment::LEFT)
                ->setBackgroundColor(Color::rgb(255, 165, 0))
                ->setFontColor(Color::rgb(139, 69, 19)),
            5 => $baseStyle // Published column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setFontBold()
                ->setBackgroundColor($this->getPublishedColor($value))
                ->setFontColor(Color::WHITE),
            6 => $baseStyle // Published At column
                ->setCellAlignment(CellAlignment::CENTER)
                ->setBackgroundColor(Color::rgb(186, 85, 211))
                ->setFontColor(Color::rgb(75, 0, 130)),
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

    private function getPublishedColor(string $published): string
    {
        return $published === __('Yes')
            ? Color::rgb(0, 128, 0)
            : Color::rgb(220, 20, 60);
    }
}
