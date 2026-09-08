<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        /** @var Vendor $vendor */
        $vendor = Auth::guard('vendor')->user();

        $clients = $vendor->oauthClients()->where('revoked', false)->latest()->get();

        return view('vendor.dashboard', [
            'vendor' => $vendor,
            'clients' => $clients,
        ]);
    }

    public function generateEmbedKey(): RedirectResponse
    {
        /** @var Vendor $vendor */
        $vendor = Auth::guard('vendor')->user();
        $origins = collect(explode(',', (string) request('allowed_origins')))
            ->map(fn (string $origin): string => trim($origin))
            ->filter()
            ->values()
            ->all();
        $key = 'pk_live_'.Str::random(32);

        $vendor->forceFill([
            'embed_key' => $key,
            'allowed_origins' => $origins ?: null,
        ])->save();

        $apiBase = config('app.url').'/api/v1/vendor/public';
        $snippet = sprintf(
            "<trade-in-calculator\n    api-base=\"%s\"\n    client-id=\"%s\"\n    language=\"en\"\n></trade-in-calculator>\n\n<script src=\"%s/vendor/trade-in-widget.js\" defer></script>\n<script>\n    const calculator = document.querySelector('trade-in-calculator');\n\n    calculator.addEventListener('onaccepted', (event) => {\n        console.log('Trade-in accepted', event.detail);\n    });\n\n    calculator.addEventListener('onrejected', (event) => {\n        console.log('Trade-in rejected', event.detail);\n    });\n</script>",
            $apiBase,
            $key,
            config('app.url')
        );

        return redirect()->route('vendor.dashboard')->with([
            'newEmbedKey' => $key,
            'embedSnippet' => $snippet,
        ]);
    }
}
