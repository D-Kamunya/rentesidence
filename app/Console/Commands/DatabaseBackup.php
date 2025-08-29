<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseBackup extends Command
{
    protected $signature = 'backup:database';
    protected $description = 'Backup the database to database/backups/';

    public function handle()
    {
        $db = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        // Detect OS and set mysqldump path
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows: Set full path to mysqldump (adjust XAMPP/WAMP path)
            $mysqldumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
        } else {
            // Linux/Mac: Use mysqldump from system PATH
            $mysqldumpPath = 'mysqldump';
        }

        // Backup path
        $backupPath = base_path('database/backups');
        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $filename = $backupPath . '/' . date('Y-m-d_H-i-s') . '_backup.sql';

        $command = "\"{$mysqldumpPath}\" --user={$user} --password={$pass} --host={$host} {$db} > \"{$filename}\"";

        system($command, $output);

        if ($output === 0) {
            $this->info("Backup created: {$filename}");
        } else {
            $this->error("Backup failed.");
        }
    }
}
