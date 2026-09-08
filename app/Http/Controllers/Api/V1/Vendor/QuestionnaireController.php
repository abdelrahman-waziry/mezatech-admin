<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * @group Vendor API - Questionnaire
 */
class QuestionnaireController extends Controller
{
    /**
     * Get the device trade-in assessment questionnaire schema
     *
     * Returns the JSON schema describing the trade-in wizard steps, questions
     * and options so vendors can render the assessment flow in their own UI.
     *
     * @authenticated
     *
     * @response 200 scenario="success" {
     *   "data": {
     *     "version": "1.0",
     *     "wizard_title": "Device Trade-In Assessment",
     *     "steps": []
     *   }
     * }
     */
    public function tradeIn(): JsonResponse
    {
        return response()->json([
            'data' => config('questionnaires.device_trade_in'),
        ]);
    }
}
