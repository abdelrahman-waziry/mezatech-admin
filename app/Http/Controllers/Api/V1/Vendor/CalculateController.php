<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Services\BestRepairCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * @group Vendor API - Calculate
 */
class CalculateController extends Controller
{
    /**
     * Calculate a repair price
     *
     * Forwards the variant, customer and selected part conditions to the Best Repair
     * pricing engine and returns its authoritative valuation.
     * Only variants within this vendor's whitelisted catalog are considered;
     * a variant outside the vendor's scope returns 404.
     *
     * @authenticated
     *
     * @bodyParam variantId int required The variant ID to price. Example: 10
     * @bodyParam customer object required Customer contact details.
     * @bodyParam customer.name string required Example: Jane Doe
     * @bodyParam customer.email string required Example: jane@example.com
     * @bodyParam customer.phoneNumber string required Example: 01000000000
     * @bodyParam repairedPartsBefore int[] Part IDs previously repaired locally/3rd-party. Example: [4, 12]
     * @bodyParam partsWithValuesMap object required Map of part_id => selected value/condition. Example: {"5": 1}
     *
     * @response 200 scenario="success" {
     *   "data": {"variantPrice": 90, "partsTotal": 50, "finalPrice": 140}
     * }
     */
    public function store(Request $request, BestRepairCalculationService $calculator): JsonResponse
    {
        $validated = $request->validate([
            'variantId' => 'required|integer|min:1',
            'customer' => 'required|array',
            'customer.name' => 'required|string',
            'customer.email' => 'required|email',
            'customer.phoneNumber' => 'required|string',
            'repairedPartsBefore' => 'nullable|array',
            'repairedPartsBefore.*' => 'integer',
            'partsWithValuesMap' => 'required|array',
        ]);

        try {
            $finalPrice = $calculator->calculate(
                (int) $validated['variantId'],
                $validated['customer'],
                $validated['repairedPartsBefore'] ?? [],
                $validated['partsWithValuesMap']
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }

        return response()->json([
            'data' => [
                'finalPrice' => $finalPrice,
            ],
        ]);
    }
}
