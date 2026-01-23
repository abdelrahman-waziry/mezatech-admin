<?php

namespace App\Http\Controllers;


use App\Models\TradeInRequest;
use Illuminate\Http\Request;

class TradeInRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $requests = TradeInRequest::orderByDesc('created_at')->paginate(15);
        return response()->json([
            'data' => $requests->map(function ($request) {
                return $this->formatRequest($request);
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $input = $request->all();

        // Map nested customer object to flat structure if present
        if ($request->has('customer') && is_array($request->input('customer'))) {
            $customer = $request->input('customer');
            $input['customerName'] = $customer['name'] ?? null;
            $input['customerEmail'] = $customer['email'] ?? null;
            $input['customerPhone'] = $customer['phoneNumber'] ?? null;
        }
        
        // Default values for missing fields
        $input['status'] = $input['status'] ?? 'pending';
        $input['tradeInQuote'] = $input['tradeInQuote'] ?? 0;

        // Replace request input with mapped data for validation
        $request->merge($input);

        $validated = $request->validate([
            'variantId' => 'required|integer',
            'productId' => 'sometimes|integer',
            'tradeInQuote' => 'required|numeric',
            'customerName' => 'required|string',
            'customerEmail' => 'required|email',
            'customerPhone' => 'required|string',
            'status' => 'required|in:pending,accepted,rejected',
            'selectedOptions' => 'sometimes|array',
            'customerAnswers' => 'sometimes|array',
        ]);

        $tradeInRequest = TradeInRequest::create([
            'variant_id' => $validated['variantId'],
            'product_id' => $validated['productId'] ?? null,
            'trade_in_quote' => $validated['tradeInQuote'],
            'customer_name' => $validated['customerName'],
            'customer_email' => $validated['customerEmail'],
            'customer_phone' => $validated['customerPhone'],
            'status' => $validated['status'],
            'selected_options' => $validated['selectedOptions'] ?? null,
            'customer_answers' => $validated['customerAnswers'] ?? null,
        ]);

        return response()->json($this->formatRequest($tradeInRequest), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $request = TradeInRequest::findOrFail($id);
        return response()->json($this->formatRequest($request));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $tradeInRequest = TradeInRequest::findOrFail($id);
        
        $validated = $request->validate([
            'variantId' => 'sometimes|integer',
            'productId' => 'sometimes|integer',
            'tradeInQuote' => 'sometimes|numeric',
            'customerName' => 'sometimes|string',
            'customerEmail' => 'sometimes|email',
            'customerPhone' => 'sometimes|string',
            'status' => 'sometimes|in:pending,accepted,rejected',
            'selectedOptions' => 'sometimes|array',
            'customerAnswers' => 'sometimes|array',
        ]);

        $updateData = [];
        if (isset($validated['variantId'])) $updateData['variant_id'] = $validated['variantId'];
        if (isset($validated['productId'])) $updateData['product_id'] = $validated['productId'];
        if (isset($validated['tradeInQuote'])) $updateData['trade_in_quote'] = $validated['tradeInQuote'];
        if (isset($validated['customerName'])) $updateData['customer_name'] = $validated['customerName'];
        if (isset($validated['customerEmail'])) $updateData['customer_email'] = $validated['customerEmail'];
        if (isset($validated['customerPhone'])) $updateData['customer_phone'] = $validated['customerPhone'];
        if (isset($validated['status'])) $updateData['status'] = $validated['status'];
        if (isset($validated['selectedOptions'])) $updateData['selected_options'] = $validated['selectedOptions'];
        if (isset($validated['customerAnswers'])) $updateData['customer_answers'] = $validated['customerAnswers'];

        $tradeInRequest->update($updateData);

        return response()->json($this->formatRequest($tradeInRequest));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $request = TradeInRequest::findOrFail($id);
        $request->delete();
        return response()->noContent();
    }

    private function formatRequest(TradeInRequest $request)
    {
        return [
            'id' => $request->id,
            'productId' => $request->product_id,
            'variantId' => $request->variant_id,
            'tradeInQuote' => (float) $request->trade_in_quote,
            'customerName' => $request->customer_name,
            'customerEmail' => $request->customer_email,
            'customerPhone' => $request->customer_phone,
            'status' => $request->status,
            'selectedOptions' => $request->selected_options,
            'customerAnswers' => $request->customer_answers,
            'createdAt' => $request->created_at->toIso8601String(),
        ];
    }
}
