<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Passport\Client;

class CredentialController extends Controller
{
    /**
     * Revoke any existing client and issue a fresh client_credentials client for the vendor.
     */
    public function store(): RedirectResponse
    {
        /** @var Vendor $vendor */
        $vendor = Auth::guard('vendor')->user();

        $vendor->oauthClients()->where('revoked', false)->update(['revoked' => true]);

        $client = new Client([
            'name' => $vendor->company_name.' API Client',
            'secret' => Str::random(40),
            'redirect_uris' => [],
            'grant_types' => ['client_credentials'],
            'revoked' => false,
        ]);
        $client->owner()->associate($vendor);
        $client->save();

        return redirect()->route('vendor.dashboard')->with([
            'newClientId' => $client->id,
            'newClientSecret' => $client->plainSecret,
        ]);
    }
}
