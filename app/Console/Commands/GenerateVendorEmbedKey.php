<?php

namespace App\Console\Commands;

use App\Models\Vendor;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateVendorEmbedKey extends Command
{
    protected $signature = 'vendor:embed-key {vendor_id} {--origins=* : Allowed origin hosts, e.g. www.example.com}';

    protected $description = 'Generate (or rotate) a public embed key for a vendor, used by the trade-in widget';

    public function handle(): int
    {
        $vendor = Vendor::find($this->argument('vendor_id'));

        if (! $vendor) {
            $this->error('Vendor not found.');

            return 1;
        }

        $key = 'pk_live_'.Str::random(32);

        $vendor->embed_key = $key;
        $vendor->allowed_origins = $this->option('origins') ?: null;
        $vendor->save();

        $this->info("Embed key generated for vendor #{$vendor->id}: {$key}");
        $this->line('');
        $this->line('Embed snippet:');
        $this->line(sprintf(
            '<trade-in-calculator api-base="%s" client-id="%s" primary-color="#2563eb"></trade-in-calculator>'."\n".
            '<script src="%s/vendor/trade-in-widget.js" defer></script>',
            config('app.url').'/api/v1/vendor/public',
            $key,
            config('app.url')
        ));

        return 0;
    }
}
