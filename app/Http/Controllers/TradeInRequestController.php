<?php

namespace App\Http\Controllers;

use App\Mail\TradeInStatusChanged;
use App\Models\TradeInRequest;
use App\Models\Variant;
use App\Services\TradeInQuoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class TradeInRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['sometimes', Rule::in(['pending', 'accepted', 'rejected', 'confirmed', 'closed'])],
            'search' => 'sometimes|string|max:100',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1',
        ]);
        $query = TradeInRequest::query()->orderByDesc('created_at');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $idSearch = preg_replace('/^TI-/i', '', $term);
            $query->where(function ($query) use ($term, $idSearch) {
                $query->where('customer_name', 'like', '%' . $term . '%')
                    ->orWhere('customer_email', 'like', '%' . $term . '%')
                    ->orWhere('customer_phone', 'like', '%' . $term . '%')
                    ->when(ctype_digit((string) $idSearch), function ($query) use ($idSearch) {
                        $query->orWhere('id', (int) $idSearch);
                    });
            });
        }

        $requests = $query->paginate($filters['per_page'] ?? 15);

        return response()->json([
            'data' => $requests->getCollection()->map(function ($request) {
                return $this->formatRequest($request);
            }),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, TradeInQuoteService $quoteService)
    {
        $input = $request->all();

        // Map nested customer object to flat structure if present
        if ($request->has('customer') && is_array($request->input('customer'))) {
            $customer = $request->input('customer');
            $input['customerName'] = $customer['name'] ?? null;
            $input['customerEmail'] = $customer['email'] ?? null;
            $input['customerPhone'] = $customer['phoneNumber'] ?? null;
        }
        
        // Replace request input with mapped data for validation
        $request->merge($input);

        $validated = $request->validate([
            'variantId' => 'required|integer|exists:variants,id',
            'productId' => 'sometimes|integer',
            'tradeInQuote' => 'sometimes|numeric|min:0',
            'customerName' => 'required|string',
            'customerEmail' => 'required|email',
            'customerPhone' => 'required|string',
            'selectedOptions' => 'sometimes|array',
            'customerAnswers' => 'sometimes|array',
            'conditionAnswers' => 'required|array',
        ]);

        $variant = Variant::findOrFail($validated['variantId']);
        $quote = $quoteService->calculate(
            (int) $validated['variantId'],
            $validated['conditionAnswers']
        );

        $tradeInRequest = TradeInRequest::create([
            'variant_id' => $validated['variantId'],
            'product_id' => $variant->product_id,
            'trade_in_quote' => $quote,
            'customer_name' => $validated['customerName'],
            'customer_email' => $validated['customerEmail'],
            'customer_phone' => $validated['customerPhone'],
            'status' => 'pending',
            'selected_options' => $validated['selectedOptions'] ?? null,
            'customer_answers' => $validated['customerAnswers'] ?? null,
            'condition_answers' => $validated['conditionAnswers'],
        ]);

        return response()->json($this->formatRequest($tradeInRequest), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $request = TradeInRequest::findOrFail($id);
        return response()->json(['data' => $this->formatRequest($request)]);
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
            'status' => 'sometimes|in:pending,accepted,rejected,confirmed,closed',
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

        // Send acceptance email when status is changed to 'accepted'
        if (isset($updateData['status']) && $updateData['status'] === 'accepted') {
            try {
                Mail::to($tradeInRequest->customer_email)
                    ->send(new TradeInStatusChanged($tradeInRequest));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send trade-in acceptance email: ' . $e->getMessage());
            }
        }

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
            'conditionAnswers' => $request->condition_answers,
            'createdAt' => $request->created_at->toIso8601String(),
        ];
    }
}
