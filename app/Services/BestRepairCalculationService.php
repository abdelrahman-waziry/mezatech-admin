<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Calls the Best Repair external API's authoritative /products/calculate endpoint
 * instead of computing the trade-in price locally.
 */
class BestRepairCalculationService
{
    protected Client $httpClient;

    protected string $baseUrl;

    public function __construct(protected ApiTokenService $tokenService, ?Client $httpClient = null)
    {
        $this->baseUrl = config('services.best_repair.base_url');
        $this->httpClient = $httpClient ?? new Client();
    }

    /**
     * @param  array{name: string, email: string, phoneNumber: string}  $customer
     * @param  array<int, int>  $repairedPartsBefore
     * @param  array<int|string, int|string>  $partsWithValuesMap
     */
    public function calculate(int $variantId, array $customer, array $repairedPartsBefore, array $partsWithValuesMap): float
    {
        $token = $this->tokenService->getToken();

        try {
            $response = $this->httpClient->post($this->baseUrl.'/products/calculate', [
                'headers' => [
                    'Authorization' => 'Bearer '.$token,
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'variantId' => $variantId,
                    'customer' => $customer,
                    'repairedPartsBefore' => array_values($repairedPartsBefore),
                    'partsWithValuesMap' => (object) array_map('strval', $partsWithValuesMap),
                ],
                'timeout' => 15,
            ]);
        } catch (\Throwable $e) {
            Log::error('Best Repair calculate API request failed: '.$e->getMessage());

            throw new RuntimeException('Unable to reach the pricing service.', previous: $e);
        }

        $data = json_decode($response->getBody(), true);

        $price = $data['price'] ?? $data['data']['price'] ?? $data['finalPrice'] ?? $data['data']['finalPrice'] ?? null;

        if ($price === null || ! is_numeric($price)) {
            Log::error('Best Repair calculate API returned unexpected response', ['body' => $data]);

            throw new RuntimeException('Unexpected response from the pricing service.');
        }

        return (float) $price;
    }
}
