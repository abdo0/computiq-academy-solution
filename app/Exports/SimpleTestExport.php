<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SimpleTestExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['John Doe', 'john@example.com', 'Active'],
            ['Jane Smith', 'jane@example.com', 'Inactive'],
            ['Bob Johnson', 'bob@example.com', 'Active'],
        ];
    }

    public function headings(): array
    {
        return ['Name', 'Email', 'Status'];
    }
}
