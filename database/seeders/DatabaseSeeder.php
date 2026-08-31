<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            // UserSeeder::class,
            // LanguageSeeder::class,
            // CurrencySeeder::class,
            // MetaSettingSeeder::class,
            // FileManagerSeeder::class,
            // InvoiceTypeSeeder::class,
            // GatewaySeeder::class,
            // SettingSeeder::class,

            // Centresidence production content — module catalog + partner KB.
            // Idempotent and demo-data-free, so `php artisan db:seed` ships the
            // configured environment to live without manual setup.
            BrandingSeeder::class,
            SystemDefaultsSeeder::class,
            CentresidenceCatalogSeeder::class,
            KnowledgeBaseSeeder::class,
            OwnerKnowledgeBaseSeeder::class,
            AffiliateKnowledgeBaseSeeder::class,
            AffiliateAcademySeeder::class,
        ]);
    }
}
