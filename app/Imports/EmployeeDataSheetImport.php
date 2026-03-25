<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EmployeeDataSheetImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            0 => new EmployeeExcelImport, // Only import the first sheet (Employee Data)
        ];
    }
}
