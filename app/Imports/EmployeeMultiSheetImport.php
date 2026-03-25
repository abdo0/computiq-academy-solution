<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EmployeeMultiSheetImport implements WithMultipleSheets
{
    protected $mainImport;

    public function __construct()
    {
        $this->mainImport = new EmployeeExcelImport;
    }

    public function sheets(): array
    {
        return [
            0 => $this->mainImport, // Only import the first sheet (Employee Data)
            // Skip sheet 1 (Dropdown Data) by not including it
        ];
    }

    /**
     * Delegate methods to the main import
     */
    public function getImportResults(): array
    {
        return $this->mainImport->getImportResults();
    }

    public function getErrors(): array
    {
        return $this->mainImport->getErrors();
    }

    public function getSummary(): array
    {
        return $this->mainImport->getSummary();
    }
}
