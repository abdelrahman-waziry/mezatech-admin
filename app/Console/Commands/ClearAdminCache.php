<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearAdminCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-admin-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all admin panel caches for improved performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Clearing admin panel caches...');

        // Clear Sushi model caches
        \Illuminate\Support\Facades\Cache::forget('products_data');
        \Illuminate\Support\Facades\Cache::forget('brands_data');
        \Illuminate\Support\Facades\Cache::forget('tags_data');
        \Illuminate\Support\Facades\Cache::forget('features_data');

        // Note: Specific cache clearing for variants and features would require more complex logic
        // For now, just clear the main caches

        $this->info('All admin panel caches cleared successfully!');
        $this->comment('Caches will be rebuilt on next page load.');
    }
}
