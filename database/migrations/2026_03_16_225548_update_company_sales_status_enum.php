<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            ALTER TABLE companies 
            MODIFY sales_status ENUM(
                'prospect',
                'contacted',
                'demo_done',
                'client',
                'inactive'
            ) DEFAULT 'prospect'
        ");
    }

    public function down()
    {
        DB::statement("
            ALTER TABLE companies 
            MODIFY sales_status ENUM(
                'prospect',
                'contacted',
                'demo_done',
                'client',
                'not_interested'
            ) DEFAULT 'prospect'
        ");
    }
};