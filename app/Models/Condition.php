<?php

namespace App\Models;

use App\Services\ApiTokenService;
use App\Services\ConditionService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Sushi\Sushi;

class Condition extends Model
{
    use Sushi;

    protected $table = 'conditions';

    protected $fillable = [
        'id',
        'name',
        'description',
        'price_modifier',
        'created_at',
        'updated_at',
    ];

    protected $schema = [
        'id' => 'integer',
        'name' => 'string',
        'description' => 'string',
        'price_modifier' => 'float',
        'created_at' => 'string',
        'updated_at' => 'string',
    ];

    protected $casts = [
        'id' => 'integer',
        'price_modifier' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getRows(): array
    {   
        try {
            $token = app(ApiTokenService::class)->getToken();
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->timeout(30)->get('https://bestrepairegypt.com/v1/conditions');

            if (!$response->successful()) {
                Log::error('Conditions API failed', ['status' => $response->status()]);
                return [];
            }

            $payload = $response->json();
            $conditions = $this->resolveRows($payload, 'conditions');

            return collect($conditions)->map(function ($condition) {
                $createdAt = $this->normalizeDate($condition['createdAt'] ?? $condition['created_at'] ?? now());
                $updatedAt = $this->normalizeDate($condition['updatedAt'] ?? $condition['updated_at'] ?? now());

                return [
                    'id' => $condition['id'] ?? null,
                    'name' => $condition['name'] ?? 'Unknown',
                    'description' => $condition['description'] ?? null,
                    'price_modifier' => isset($condition['priceModifier'])
                        ? (float) $condition['priceModifier']
                        : (float) ($condition['price_modifier'] ?? 1.0),
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ];
            })->filter(fn ($row) => $row['id'] !== null)->all();
        } catch (\Exception $e) {
            Log::error('Failed to fetch conditions from API', ['error' => $e->getMessage()]);
            return [];
        }
    }

    protected function resolveRows(array $payload, ?string $collectionKey = null): array
    {
        if ($collectionKey && isset($payload[$collectionKey]) && is_array($payload[$collectionKey])) {
            return $payload[$collectionKey];
        }

        if (isset($payload['data']) && is_array($payload['data'])) {
            if ($collectionKey && isset($payload['data'][$collectionKey]) && is_array($payload['data'][$collectionKey])) {
                return $payload['data'][$collectionKey];
            }

            if (isset($payload['data']['items']) && is_array($payload['data']['items'])) {
                return $payload['data']['items'];
            }

            return $payload['data'];
        }

        if (isset($payload['items']) && is_array($payload['items'])) {
            return $payload['items'];
        }

        return is_array($payload) ? array_values($payload) : [];
    }

    protected function normalizeDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->toDateTimeString();
        }

        try {
            return Carbon::parse($value)->toDateTimeString();
        } catch (\Exception $e) {
            return now()->toDateTimeString();
        }
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    protected $keyType = 'int';

    public function save(array $options = [])
    {
        try {
            $service = new ConditionService();

            if ($this->exists && $this->id) {
                $result = $service->update((string) $this->id, $this->attributesToArray());
            } else {
                $result = $service->create($this->attributesToArray());
            }

            if (isset($result['id'])) {
                $this->setAttribute('id', $result['id']);
                $this->exists = true;
            }

            Log::info('Condition saved via API', ['id' => $this->id ?? 'new']);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to save condition: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete(): bool
    {
        try {
            $service = new ConditionService();
            $service->delete((string) $this->id);

            Log::info('Condition deleted via API', ['id' => $this->id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete condition: ' . $e->getMessage());
            throw $e;
        }
    }
}
