<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('db:run-setup-sql', function () {
    $file = 'rentesidence-setup.sql';
    $path = base_path($file);

    if (!File::exists($path)) {
        $this->error("File not found: {$path}");
        return;
    }

    $sql = File::get($path);

    DB::unprepared($sql);

    $this->info('SQL file executed successfully.');
})->describe('Run SQL queries from a specified file');


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
