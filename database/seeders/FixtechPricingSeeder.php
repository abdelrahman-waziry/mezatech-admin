<?php

namespace Database\Seeders;

use App\Traits\HasFixtechImport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FixtechPricingSeeder extends Seeder
{
    use HasFixtechImport;

    protected string $filePath = __DIR__ . '/data/fixtech_Pricing_Guide.xlsx';

    public function run(): void
    {
        $this->command->info('Loading Excel file...');
        $spreadsheet = IOFactory::load($this->filePath);

        $this->command->info('Truncating tables...');
        $this->truncateTables();

        $this->command->info('Seeding repair sheets...');
        $this->seedRepairSheets($spreadsheet);

        $this->command->info('Seeding accessory sheets...');
        $this->seedAccessorySheets($spreadsheet);

        $this->command->info('✅ Done!');
    }

    protected function truncateTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('repair_prices')->truncate();
        DB::table('repair_subcategories')->truncate();
        DB::table('repair_categories')->truncate();
        DB::table('accessories')->truncate();
        DB::table('accessory_categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}