<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Services\PriceCalculationService;
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
    * Calculates the valuation from the Admin catalog and pricing rules.
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
    public function store(Request $request, PriceCalculationService $calculator): JsonResponse
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
            $calculation = $calculator->calculate(
                (int) $validated['variantId'],
                $validated['partsWithValuesMap'],
                $validated['repairedPartsBefore'] ?? []
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }

        return response()->json([
            'data' => [
                'variantPrice' => $calculation['variantPrice'],
                'partsTotal' => $calculation['partsTotal'],
                'finalPrice' => $calculation['finalPrice'],
            ],
        ]);
    }
}
