<?php

namespace App\Models;

use App\Services\ApiTokenService;
use App\Services\TagService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Tag extends Model
{
    protected $table = 'tags';
    // Static cache for request-scoped caching
    protected static array $requestCache = [];

    protected $fillable = [
        'id',
        'name',
    ];

    protected $schema = [
        'id' => 'integer',
        'name' => 'string',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public function getRows(): array
    {
        return static::query()->get()->toArray();

        // Smart Caching: Request-scoped cache
        if (isset(static::$requestCache['tags_data'])) {
             return static::$requestCache['tags_data'];
        }
        
        try {
                $token = app(ApiTokenService::class)->getToken();
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ])->timeout(30)->get('https://bestrepairegypt.com/v1/tags');

            if (!$response->successful()) {
                Log::error('Tags API failed', ['status' => $response->status()]);
                return [];
            }

            $data = $response->json();

            // Extract tags from response according to openapi.yaml TagsResponse schema
            // TagsResponse has: { tags: TagDTO[] }
            // TagDTO has: { id: int64, name: string }
            if (isset($data['tags']) && is_array($data['tags'])) {
                $tags = $data['tags'];
            } elseif (is_array($data)) {
                // Fallback: if response is directly an array
                $tags = $data;
            } else {
                Log::warning('Tags API did not return expected structure', ['response' => $data]);
                return [];
            }

            $rows = collect($tags)->map(function ($tag) {
                return [
                    'id' => $tag['id'] ?? null,
                    'name' => $tag['name'] ?? 'Unnamed',
                ];
            })->filter(fn ($row) => $row['id'] !== null)->all();
            
            static::$requestCache['tags_data'] = $rows;
            return $rows;
        } catch (\Exception $e) {
            Log::error('Failed to fetch tags from API', ['error' => $e->getMessage()]);
            return [];
        }
    }


}
