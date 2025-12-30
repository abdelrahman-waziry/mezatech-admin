<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ApiTokenService
{
    protected $httpClient;
    protected $baseUrl = 'https://bestrepairegypt.com/v1';
    protected $cacheKey = 'best_repair_api_token';
    protected $tokenExpiryKey = 'best_repair_api_token_expires_at';
    protected $defaultTokenTtl = 3600; // 1 hour default, will be overridden by API response if available

    public function __construct()
    {
        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);
    }

    /**
     * Get the current API token, refreshing if expired
     */
    public function getToken(): ?string
    {
        // Check if token exists and is still valid
        $token = Cache::get($this->cacheKey);
        $expiresAt = Cache::get($this->tokenExpiryKey);

        // If token exists and hasn't expired, return it
        if ($token && $expiresAt && now()->timestamp < $expiresAt) {
            return $token;
        }

        // Token expired or doesn't exist, fetch a new one
        return $this->fetchToken();
    }

    /**
     * Fetch a new token from the API
     */
    public function fetchToken(): ?string
    {
        try {
            $email = "a@b.c";
            $password = "123654789@A!";
            

            if (!$email || !$password) {
                Log::error('BEST_REPAIR_API_EMAIL and BEST_REPAIR_API_PASSWORD must be set in .env');
                return null;
            }

            $response = $this->httpClient->post('/v1/admin/login', [
                'json' => [
                    'email' => $email,
                    'password' => $password,
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            if (!isset($data['token'])) {
                Log::error('API did not return a token in the response', ['response' => $data]);
                return null;
            }

            $token = $data['token'];
            $expiresIn = $data['expires_in'] ?? $this->defaultTokenTtl;

            // Calculate expiration timestamp (subtract 60 seconds as buffer)
            $expiresAt = now()->timestamp + $expiresIn - 60;

            // Store token and expiration in cache
            Cache::put($this->cacheKey, $token, now()->addSeconds($expiresIn));
            Cache::put($this->tokenExpiryKey, $expiresAt, now()->addSeconds($expiresIn));

            Log::info('Successfully fetched new API token', ['expires_in' => $expiresIn]);

            return $token;
        } catch (\Exception $e) {
            Log::error('Failed to fetch API token', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return the token from env as fallback if available
            return env('BEST_REPAIR_API_TOKEN');
        }
    }

    /**
     * Check if the current token is expired or will expire soon
     */
    public function isTokenExpired(int $bufferSeconds = 300): bool
    {
        $expiresAt = Cache::get($this->tokenExpiryKey);

        if (!$expiresAt) {
            return true;
        }

        // Consider token expired if it expires within the buffer time
        return now()->timestamp >= ($expiresAt - $bufferSeconds);
    }

    /**
     * Clear the cached token (force refresh on next request)
     */
    public function clearToken(): void
    {
        Cache::forget($this->cacheKey);
        Cache::forget($this->tokenExpiryKey);
    }
}

