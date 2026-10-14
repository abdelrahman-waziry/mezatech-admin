<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(): JsonResponse
    {
        $customers = Customer::query()->get()->map->toArray()->values()->all();

        return response()->json(['customers' => $customers]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phoneNumber' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $data['phone_number'] = $data['phoneNumber'] ?? null;

        $customer = Customer::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone_number' => $data['phoneNumber'] ?? null,
            'password' => $data['password'],
        ]);
        return response()->json($customer, 201);
    }

    public function show(string $id): JsonResponse
    {
        $customer = Customer::find($id);

        return $customer
            ? response()->json($customer)
            : response()->json(['message' => 'Customer not found'], 404);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255'],
            'phoneNumber' => ['sometimes', 'nullable', 'string', 'max:50'],
            'password' => ['sometimes', 'string', 'min:8'],
        ]);

        if (array_key_exists('phoneNumber', $data)) {
            $data['phone_number'] = $data['phoneNumber'];
            unset($data['phoneNumber']);
        }

        $customer = Customer::findOrFail($id);
        $customer->update($data);
        return response()->json($customer);
    }

    public function destroy(string $id): JsonResponse
    {
        Customer::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}