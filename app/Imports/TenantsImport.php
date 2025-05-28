<?php

namespace App\Imports;

use App\Models\Tenant;
use Maatwebsite\Excel\Concerns\ToModel;

class TenantsImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Tenant([
            //
        ]);
    }
}
