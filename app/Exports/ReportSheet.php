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

class ReportSheet implements FromArray, WithColumnWidths, WithEvents, WithHeadings, WithStyles
{
    protected string $type;

    protected array $reportData;

    protected string $locale;

    public function __construct(string $type, array $reportData, string $locale = 'en')
    {
        $this->type = $type;
        $this->reportData = $reportData;
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

    /**
     * Get translated field value from translatable models
     */
    private function getTranslatedField($model, string $field, ?string $locale = null): string
    {
        if (! $model) {
            return '';
        }

        $locale = $locale ?? $this->locale;

        // Check if the model uses HasTranslations trait
        if (method_exists($model, 'getTranslation')) {
            return $model->getTranslation($field, $locale) ?? '';
        }

        // Fallback to direct property access
        return $model->{$field} ?? '';
    }

    public function array(): array
    {
        $data = [];

        foreach ($this->reportData['records'] as $record) {
            $data[] = $this->getRow($record, $this->type);
        }

        return $data;
    }

    public function headings(): array
    {
        return $this->getHeaders($this->type);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,  // ID
            'B' => 30,  // Name/Title
            'C' => 25,  // Description/Email
            'D' => 18,  // Status
            'E' => 15,  // Priority/Phone
            'F' => 20,  // Date fields
            'G' => 20,  // Assigned To/Branch
            'H' => 15,  // Value/Budget
            'I' => 20,  // Additional fields
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $this->getSectionColor()],
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
                $sheet->setTitle(ucfirst($this->type));

                // Style all data rows with alternating colors
                $rowCount = count($this->reportData['records']);
                $headers = $this->getHeaders($this->type);
                $lastColumn = chr(65 + count($headers) - 1);

                for ($row = 2; $row <= $rowCount + 1; $row++) {
                    $isEven = ($row - 2) % 2 === 0;
                    $bgColor = $isEven ? 'F8F9FA' : 'FFFFFF';
                    $textColor = '2C3E50';

                    $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                        'font' => [
                            'size' => 10,
                            'color' => ['rgb' => $textColor],
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $bgColor],
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

                    // Center align ID column
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Right align numeric columns
                    if (in_array($this->type, ['marketing_campaign'])) {
                        $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    }
                }

                // Freeze the header row
                $sheet->freezePane('A2');

                // Set default row height
                $sheet->getDefaultRowDimension()->setRowHeight(20);
            },
        ];
    }

    private function getSectionColor(): string
    {
        $colors = [
            'client' => '2E86AB',
            'complaint' => 'D62828',
            'announcement' => 'F77F00',
            'marketing_campaign' => 'FCBF49',

        ];

        return $colors[$this->type] ?? '4472C4';
    }

    private function getHeaders(string $type): array
    {
        switch ($type) {
            case 'client':
                return [
                    $this->translateText('ID'),
                    $this->translateText('Name'),
                    $this->translateText('Email'),
                    $this->translateText('Phone'),

                    $this->translateText('Status'),
                    $this->translateText('Created At'),
                ];
            case 'complaint':
                return [
                    $this->translateText('ID'),
                    $this->translateText('Title'),
                    $this->translateText('Description'),
                    $this->translateText('Status'),
                    $this->translateText('Priority'),
                    $this->translateText('Client'),
                    $this->translateText('Created At'),
                ];
            case 'announcement':
                return [
                    $this->translateText('ID'),
                    $this->translateText('Title'),
                    $this->translateText('Content'),
                    $this->translateText('Status'),
                    $this->translateText('Created By'),
                    $this->translateText('Created At'),
                ];
            case 'marketing_campaign':
                return [
                    $this->translateText('ID'),
                    $this->translateText('Name'),
                    $this->translateText('Description'),

                    $this->translateText('Assigned To'),
                    $this->translateText('Status'),
                    $this->translateText('Budget'),
                    $this->translateText('Created At'),
                ];

            default:
                return [
                    $this->translateText('ID'),
                    $this->translateText('Name'),
                    $this->translateText('Created At'),
                ];
        }
    }

    private function getRow($record, string $type): array
    {
        try {
            switch ($type) {
                case 'client':
                    return [
                        $record->id,
                        $record->name ?? '',
                        $record->email ?? '',
                        $record->primary_contact_phone ?? '',

                        $record->is_active ? $this->translateText('Active') : $this->translateText('Inactive'),
                        $record->created_at?->format('Y-m-d H:i:s') ?? '',
                    ];
                case 'complaint':
                    return [
                        $record->id,
                        $record->title,
                        $record->description ?? '',
                        $record->status?->getLabel() ?? '',
                        $record->priority?->getLabel() ?? '',
                        $record->client?->name ?? '',
                        $record->created_at->format('Y-m-d H:i:s'),
                    ];
                case 'announcement':
                    return [
                        $record->id,
                        $record->title,
                        $record->content ?? '',
                        $record->is_active ? $this->translateText('Active') : $this->translateText('Inactive'),
                        $record->created_at->format('Y-m-d H:i:s'),
                    ];
                case 'marketing_campaign':
                    return [
                        $record->id,
                        $record->name,
                        $record->description ?? '',

                        $record->assignedTo?->name ?? '',
                        $record->status?->getLabel() ?? '',
                        $record->budget ? '$'.number_format($record->budget, 2) : '',
                        $record->created_at->format('Y-m-d H:i:s'),
                    ];

                default:
                    return [$record->id, $record->name ?? '', $record->created_at?->format('Y-m-d H:i:s') ?? ''];
            }
        } catch (\Exception $e) {
            // Return a safe fallback row
            return [
                $record->id ?? 'N/A',
                'Error: '.$e->getMessage(),
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ];
        }
    }
}
