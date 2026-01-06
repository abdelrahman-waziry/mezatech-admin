<?php

namespace Tests\Feature;

use App\Enums\Analytics\AppSource;
use App\Enums\Analytics\Condition;
use App\Enums\Analytics\ErrorType;
use App\Enums\Analytics\EventName;
use App\Enums\Analytics\Method;
use App\Enums\Analytics\Network;
use App\Models\AnalyticsRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_ingest_request_log()
    {
        $payload = [
            'request_id' => \Illuminate\Support\Str::uuid()->toString(),
            'endpoint' => '/api/test',
            'method' => Method::GET->value,
            'timestamp' => now()->toIso8601String(),
            'app_source' => AppSource::WEB->value,
            'app_version' => '1.0.0',
            'device' => [
                'os' => 'iOS',
                'model' => 'iPhone 13',
                'network' => Network::WIFI->value,
            ],
            'response' => [
                'status' => 200,
                'duration_ms' => 150,
                'error_type' => null,
            ],
        ];

        $response = $this->postJson('/api/v1/analytics/requests', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('analytics_requests', [
            'request_id' => $payload['request_id'],
            'endpoint' => '/api/test',
        ]);
    }

    public function test_ingest_event_log()
    {
        $payload = [
            'event_name' => EventName::TRADEIN_COMPLETED->value,
            'timestamp' => now()->toIso8601String(),
            'user_id' => 'user_123',
            'context' => [
                'brand' => 'Apple',
                'model' => 'iPhone 12',
                'condition' => Condition::GOOD->value,
                'quoted_price' => 500.00,
            ],
            'location' => [
                'country' => 'SA',
                'city' => 'Riyadh',
            ],
            'device' => [
                'brand' => 'Apple',
                'model' => 'iPhone 13',
                'os_version' => '15.0',
            ],
        ];

        $response = $this->postJson('/api/v1/analytics/events', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('analytics_events', [
            'event_name' => EventName::TRADEIN_COMPLETED->value,
            'city' => 'Riyadh',
        ]);
    }

    public function test_admin_performance_endpoint()
    {
        // Seed data
        AnalyticsRequest::create([
             'request_id' => '123',
             'endpoint' => '/api/test',
             'method' => 'GET',
             'status' => 200,
             'duration_ms' => 100,
             'app_source' => 'web',
             'app_version' => '1.0',
             'device_os' => 'iOS',
             'device_model' => 'iPhone 13',
             'device_network' => 'wifi',
             'created_at' => now(),
        ]);

        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/admin/analytics/performance');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'request_count',
                'avg_response_time',
                'success_rate',
                'failure_rate',
                'date'
            ]
        ]);
    }
}
