<?php

namespace App\Models;

use App\Services\ApiTokenService;
use App\Services\CustomerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Sushi\Sushi;

class Customer extends Model
{
    use Sushi;

    protected $table = 'customers';

    protected $fillable = [
        'id',
        'name',
        'email',
        'phone_number',
    ];

    protected $schema = [
        'id' => 'integer',
        'name' => 'string',
        'email' => 'string',
        'phone_number' => 'string',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    /**
     * Fetch customers from external API
     * According to openapi.yaml: /v1/admin/customers returns CustomersResponse { customers: UserDTO[] }
     * UserDTO has: { id: int64, name: string, email: string, phoneNumber: string, password: string }
     */
    public function getRows(): array
    {
        try {
            $token = app(ApiTokenService::class)->getToken();
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->timeout(30)->get('https://bestrepairegypt.com/v1/admin/customers');

            if (!$response->successful()) {
                Log::error('Customers API failed', ['status' => $response->status()]);
                return [];
            }

            $data = $response->json();
            

            // Extract customers from response according to openapi.yaml CustomersResponse schema
            // CustomersResponse has: { customers: UserDTO[] }
            if (isset($data['customers']) && is_array($data['customers'])) {
                $customers = $data['customers'];
            } elseif (is_array($data)) {
                // Fallback: if response is directly an array
                $customers = $data;
            } else {
                Log::warning('Customers API did not return expected structure', ['response' => $data]);
                return [];
            }

            Log::info('Customers API processed: ' . count($customers) . ' items');

            $rows = collect($customers)->map(function ($customer) {
                return [
                    'id' => $customer['id'] ?? null,
                    'name' => $customer['name'] ?? 'Unknown',
                    'email' => $customer['email'] ?? null,
                    'phone_number' => $customer['phoneNumber'] ?? $customer['phone_number'] ?? null,
                ];
            })->filter(fn ($row) => $row['id'] !== null)->all();

            Log::info('Total customers mapped: ' . count($rows));

            return $rows;
        } catch (\Exception $e) {
            Log::error('Failed to fetch customers from API', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function getIncrementing()
    {
        return false;
    }

    protected $keyType = 'int';

    /**
     * Override save to use API for creating/updating customers
     */
    public function save(array $options = [])
    {
        try {
            $service = new CustomerService();

            if ($this->exists && isset($this->id)) {
                // Update existing customer
                $result = $service->update((string) $this->id, $this->attributes);
            } else {
                // Create new customer
                $result = $service->create($this->attributes);
            }

            // Update attributes with API response
            if (isset($result['id'])) {
                $this->setAttribute('id', $result['id']);
                $this->exists = true;
            }

            Log::info('Customer saved via API', ['id' => $this->id ?? 'new']);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to save customer: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Override delete to use API
     */
    public function delete()
    {
        try {
            $service = new CustomerService();
            $service->delete((string) $this->id);
            Log::info('Customer deleted via API', ['id' => $this->id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete customer: ' . $e->getMessage());
            throw $e;
        }
    }
}

