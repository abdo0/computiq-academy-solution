<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SummarySheet implements FromArray, WithColumnWidths, WithEvents, WithHeadings, WithStyles
{
    protected array $allData;

    protected string $locale;

    public function __construct(array $allData, string $locale = 'en')
    {
        $this->allData = $allData;
        $this->locale = $locale;
    }

    /**
     * Translate text using the specified locale
     */
    private function translateText(string $key, ?string $locale = null): string
    {
        $locale = $locale ?? $this->locale;
        $original = app()->getLocale();
        app()->setLocale($locale);
        $translation = __($key);
        app()->setLocale($original);

        return $translation;
    }

    public function array(): array
    {
        $data = [];

        foreach ($this->allData as $type => $reportData) {
            $count = count($reportData['records']);
            $data[] = [
                ucfirst($type),
                $count,
                $this->getDescription($type),
                $this->getLastUpdated($reportData),
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            $this->translateText('Report Type'),
            $this->translateText('Record Count'),
            $this->translateText('Description'),
            $this->translateText('Last Updated'),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 15,
            'C' => 40,
            'D' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '27AE60'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Set sheet title
                $sheet->setTitle('Summary');

                // Style data rows
                $rowCount = count($this->allData);
                $colors = [
                    'client' => '2E86AB',
                    'complaint' => 'D62828',
                    'announcement' => 'F77F00',
                    'marketing_campaign' => 'FCBF49',

                ];

                $rowIndex = 0;
                foreach ($this->allData as $type => $reportData) {
                    $row = $rowIndex + 2;
                    $color = $colors[$type] ?? '4472C4';

                    $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
                        'font' => [
                            'size' => 11,
                            'color' => ['rgb' => '2C3E50'],
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F8F9FA'],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'E9ECEF'],
                            ],
                        ],
                    ]);

                    // Add color indicator
                    $sheet->getStyle("A{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $color],
                        ],
                        'font' => [
                            'color' => ['rgb' => 'FFFFFF'],
                            'bold' => true,
                        ],
                    ]);

                    $rowIndex++;
                }

                // Add total row
                $totalRow = $rowCount + 3;
                $totalRecords = array_sum(array_map(fn ($data) => count($data['records']), $this->allData));

                $sheet->setCellValue("A{$totalRow}", 'TOTAL RECORDS');
                $sheet->setCellValue("B{$totalRow}", $totalRecords);

                $sheet->getStyle("A{$totalRow}:D{$totalRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E74C3C'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => 'C0392B'],
                        ],
                    ],
                ]);

                // Add export info
                $infoRow = $totalRow + 3;
                $sheet->setCellValue("A{$infoRow}", '📅 Exported on: '.now()->format('Y-m-d H:i:s'));
                $sheet->setCellValue('A'.($infoRow + 1), '🏢 Generated by: '.config('app.name'));

                $sheet->getStyle("A{$infoRow}:A".($infoRow + 1))->applyFromArray([
                    'font' => [
                        'size' => 9,
                        'italic' => true,
                        'color' => ['rgb' => '7F8C8D'],
                    ],
                ]);

                // Freeze header row
                $sheet->freezePane('A2');

                // Set default row height
                $sheet->getDefaultRowDimension()->setRowHeight(20);
            },
        ];
    }

    private function getDescription(string $type): string
    {
        $descriptions = [
            'client' => $this->translateText('Customer information and contact details'),
            'complaint' => $this->translateText('Customer complaints and feedback'),
            'announcement' => $this->translateText('Company announcements and news'),
            'marketing_campaign' => $this->translateText('Marketing campaigns and activities'),

        ];

        return $descriptions[$type] ?? $this->translateText('Report data');
    }

    private function getLastUpdated(array $reportData): string
    {
        if (empty($reportData['records'])) {
            return 'No data';
        }

        $latestRecord = collect($reportData['records'])->max('updated_at');

        return $latestRecord ? $latestRecord->format('Y-m-d') : 'Unknown';
    }
}
