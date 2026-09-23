<?php

namespace Database\Seeders;

use App\Models\FileManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Ships the Centresidence brand with the code: the logo + favicon travel as
 * committed assets under database/seeders/assets/brand and are installed into
 * storage on seed, so a fresh deploy is branded with no manual upload.
 *
 * Self-healing: copies the asset into storage, registers a FileManager row, and
 * points the setting at it. Idempotent (keyed by folder+file_name).
 *
 *   php artisan db:seed --class=Database\\Seeders\\BrandingSeeder
 */
class BrandingSeeder extends Seeder
{
    public function run(): void
    {
        $assets = __DIR__ . '/assets/brand';

        $this->installImageSetting('app_logo', $assets . '/cs-logo.png', 'cs-logo.png');
        $this->installImageSetting('app_fav_icon', $assets . '/cs-favicon.png', 'cs-favicon.png');

        // Login copy (the layout already renders these; keep them authoritative).
        setOption('app_name', 'Centresidence');
        setOption('sign_in_text_title', 'Welcome to Centresidence');
        setOption('sign_in_text_subtitle', 'The smarter way to manage properties, payments and infrastructure financing.');

        // Refresh the in-memory settings cache for the rest of this request.
        config(['settings' => \App\Models\Setting::pluck('option_value', 'option_key')->toArray()]);
    }

    /**
     * Copy a committed image into the Setting storage folder, register (or reuse)
     * its FileManager record, and point the given option at it. getSettingImage()
     * resolves the option → FileManager → files/Setting/{file_name}.
     */
    private function installImageSetting(string $optionKey, string $source, string $fileName): void
    {
        if (! is_file($source)) {
            return; // asset missing — leave the existing setting untouched
        }

        $disk = config('app.STORAGE_DRIVER', 'public');
        Storage::disk($disk)->put('files/Setting/' . $fileName, file_get_contents($source));

        $fm = FileManager::where('folder_name', 'files/Setting')->where('file_name', $fileName)->first();
        if (! $fm) {
            $fm = new FileManager();
            $fm->folder_name = 'files/Setting';
            $fm->file_name   = $fileName;
            $fm->file_size   = (string) filesize($source);
            $fm->save();
        }

        setOption($optionKey, (string) $fm->id);
    }
}
