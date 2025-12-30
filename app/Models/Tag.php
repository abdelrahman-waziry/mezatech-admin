<?php

namespace App\Models;

use App\Services\ApiTokenService;
use App\Services\TagService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Sushi\Sushi;

class Tag extends Model
{
    use Sushi;

    protected $table = 'tags';

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
        return \Illuminate\Support\Facades\Cache::remember('tags_data', 300, function () {
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

            return collect($tags)->map(function ($tag) {
                return [
                    'id' => $tag['id'] ?? null,
                    'name' => $tag['name'] ?? 'Unnamed',
                ];
            })->filter(fn ($row) => $row['id'] !== null)->all();
        } catch (\Exception $e) {
            Log::error('Failed to fetch tags from API', ['error' => $e->getMessage()]);
            return [];
        }
        });
    }


    public function getIncrementing(): bool
    {
        return false;
    }

    protected $keyType = 'int';

    public function save(array $options = [])
    {
        try {
            $service = new TagService();

            if ($this->exists && $this->id) {
                $result = $service->update((string) $this->id, $this->attributesToArray());
            } else {
                $result = $service->create($this->attributesToArray());
            }

            if (isset($result['id'])) {
                $this->setAttribute('id', $result['id']);
                $this->exists = true;
            }

            Log::info('Tag saved via API', ['id' => $this->id ?? 'new']);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to save tag: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete(): bool
    {
        try {
            $service = new TagService();
            $service->delete((string) $this->id);

            Log::info('Tag deleted via API', ['id' => $this->id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete tag: ' . $e->getMessage());
            throw $e;
        }
    }
}
