<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseBackup extends Command
{
    protected $signature = 'backup:database';
    protected $description = 'Backup the database without using mysqldump (handles FK issues)';

    public function handle()
    {
        $database = DB::getDatabaseName();
        $tables = DB::select('SHOW TABLES');

        $filePath = base_path('database/backups/' . date('Y-m-d_H-i-s') . '.sql');
        File::ensureDirectoryExists(dirname($filePath));
        $handle = fopen($filePath, 'w+');

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];

            // Drop table if exists
            fwrite($handle, "DROP TABLE IF EXISTS `$tableName`;\n");

            // Create statement
            $create = DB::select("SHOW CREATE TABLE `$tableName`")[0]->{'Create Table'};
            fwrite($handle, $create . ";\n\n");

            // Data inserts
            $rows = DB::table($tableName)->get();
            foreach ($rows as $row) {
                $vals = array_map(function ($v) {
                    return is_null($v) ? 'NULL' : "'" . addslashes($v) . "'";
                }, (array) $row);

                fwrite($handle, "INSERT INTO `$tableName` VALUES(" . implode(',', $vals) . ");\n");
            }
            fwrite($handle, "\n\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");

        fclose($handle);

        $this->info("Database backup saved to: $filePath");
    }
}
