<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

abstract class BaseExternalService
{
    protected $httpClient;
    protected $tokenService;
    protected $baseUrl = 'https://bestrepairegypt.com/v1';

    /**
     * Resource name for API endpoint
     * Should be implemented by child class
     */
    abstract protected function getResourceName(): string;

    public function __construct(ApiTokenService $tokenService = null)
    {
        $this->httpClient = new Client();
        $this->tokenService = $tokenService ?? app(ApiTokenService::class);
    }

    /**
     * Fetch all resources from external API
     */
    public function fetchAll(array $filters = [])
    {
        try {
            $url = $this->baseUrl . '/' . $this->getResourceName();
            
            $response = $this->httpClient->request('GET', $url, [
                'headers' => $this->getHeaders(),
                'query' => $filters,
                'timeout' => 30,
                'connect_timeout' => 10,
            ]);

            $data = json_decode($response->getBody(), true);
            Log::info("Fetched {$this->getResourceName()}", ['count' => is_array($data) ? count($data) : 0]);
            return is_array($data) ? $data : [];
        } catch (\Exception $e) {
            Log::error("Failed to fetch {$this->getResourceName()}: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Fetch single resource by ID
     */
    public function fetchOne(string $id)
    {
        try {
            $url = $this->baseUrl . '/' . $this->getResourceName() . '/' . $id;
            
            $response = $this->httpClient->request('GET', $url, [
                'headers' => $this->getHeaders(),
                'timeout' => 30,
            ]);

            $data = json_decode($response->getBody(), true);
            Log::info("Fetched {$this->getResourceName()} by ID", ['id' => $id]);
            return $data;
        } catch (\Exception $e) {
            Log::error("Failed to fetch {$this->getResourceName()} {$id}: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Create resource via API
     */
    public function create(array $data)
    {
        try {
            $url = $this->baseUrl . '/' . $this->getResourceName();
            $payload = $this->formatPayload($data);

            $response = $this->httpClient->request('POST', $url, [
                'headers' => $this->getHeaders(),
                'json' => $payload,
                'timeout' => 30,
            ]);

            $result = json_decode($response->getBody(), true);
            
            // if (empty($result['id'])) {
            //     throw new \Exception('API did not return resource ID');
            // }

            // Log::info("Created {$this->getResourceName()}", ['id' => $result['id']]);
            return $result;
        } catch (\Exception $e) {
            Log::error("Failed to create {$this->getResourceName()}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Update resource via API
     */
    public function update(string $id, array $data)
    {
        try {
            $url = $this->baseUrl . '/' . $this->getResourceName() . '/' . $id;
            $payload = $this->formatPayload($data);

            $response = $this->httpClient->request('PUT', $url, [
                'headers' => $this->getHeaders(),
                'json' => $payload,
                'timeout' => 30,
            ]);

            $result = json_decode($response->getBody(), true);
            Log::info("Updated {$this->getResourceName()}", ['id' => $id]);
            return $result;
        } catch (\Exception $e) {
            Log::error("Failed to update {$this->getResourceName()} {$id}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Delete resource via API
     */
    public function delete(string $id)
    {
        try {
            $url = $this->baseUrl . '/' . $this->getResourceName() . '/' . $id;

            $response = $this->httpClient->request('DELETE', $url, [
                'headers' => $this->getHeaders(),
                'timeout' => 30,
            ]);

            Log::info("Deleted {$this->getResourceName()}", ['id' => $id]);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to delete {$this->getResourceName()} {$id}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Format data for API payload
     * Override in child class for custom formatting
     */
    protected function formatPayload(array $data): array
    {
        return $data;
    }

    /**
     * Get HTTP headers for API requests
     */
    protected function getHeaders(): array
    {
        $token = $this->tokenService->getToken();
        
        return [
            'Authorization' => "Bearer {$token}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Get paginated resources
     */
    public function paginate(int $page = 1, int $perPage = 15)
    {
        $resources = $this->fetchAll();
        $total = count($resources);
        $paginated = array_slice($resources, ($page - 1) * $perPage, $perPage);

        return [
            'data' => $paginated,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }
}
