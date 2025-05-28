<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PropertyExcelImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Properties' => new PropertiesImport(),
            // 'Units' => new PropertyUnitsImport(),
            // 'Tenants' => new TenantsImport(),
        ];
    }
}
