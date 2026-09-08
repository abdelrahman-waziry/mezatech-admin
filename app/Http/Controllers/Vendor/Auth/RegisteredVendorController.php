<?php

namespace App\Http\Controllers\Vendor\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\RegisterVendorRequest;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class RegisteredVendorController extends Controller
{
    public function create(): View
    {
        return view('vendor.auth.register');
    }

    public function store(RegisterVendorRequest $request): RedirectResponse
    {
        $vendor = Vendor::create([
            'company_name' => $request->company_name,
            'contact_name' => $request->contact_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::guard('vendor')->login($vendor);

        return redirect()->route('vendor.dashboard');
    }
}
