<?php

namespace Tests\Feature\Api\V1\Vendor;

use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmbedAuthTest extends TestCase
{
    use RefreshDatabase;

    private function makeVendor(array $overrides = []): Vendor
    {
        return Vendor::create(array_merge([
            'company_name' => 'Acme Repairs',
            'email' => 'vendor'.uniqid().'@example.com',
            'password' => 'secret',
            'status' => 'active',
            'embed_key' => 'pk_live_test123',
            'allowed_origins' => null,
        ], $overrides));
    }

    public function test_missing_client_id_is_rejected(): void
    {
        $this->getJson('/api/v1/vendor/public/products')
            ->assertStatus(401);
    }

    public function test_invalid_client_id_is_rejected(): void
    {
        $this->getJson('/api/v1/vendor/public/products', ['X-Client-Id' => 'bogus'])
            ->assertStatus(401);
    }

    public function test_valid_client_id_is_accepted(): void
    {
        $this->makeVendor();

        $this->getJson('/api/v1/vendor/public/products', ['X-Client-Id' => 'pk_live_test123'])
            ->assertStatus(200);
    }

    public function test_disallowed_origin_is_rejected(): void
    {
        $this->makeVendor(['allowed_origins' => ['allowed.example.com']]);

        $this->withHeaders(['X-Client-Id' => 'pk_live_test123', 'Origin' => 'https://evil.example.com'])
            ->getJson('/api/v1/vendor/public/products')
            ->assertStatus(403);
    }

    public function test_allowed_origin_is_accepted(): void
    {
        $this->makeVendor(['allowed_origins' => ['allowed.example.com']]);

        $this->withHeaders(['X-Client-Id' => 'pk_live_test123', 'Origin' => 'https://allowed.example.com'])
            ->getJson('/api/v1/vendor/public/products')
            ->assertStatus(200);
    }

    public function test_calculate_requires_variant_id_and_parts_map(): void
    {
        $this->makeVendor();

        $response = $this->withHeaders(['X-Client-Id' => 'pk_live_test123'])
            ->postJson('/api/v1/vendor/public/calculate', [
                'customer' => ['name' => 'Test', 'email' => 'a@b.com', 'phoneNumber' => '0100'],
                'repairedPartsBefore' => [1, 2],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['variantId', 'partsWithValuesMap']);
    }

    public function test_suspended_vendor_is_rejected(): void
    {
        $this->makeVendor(['status' => 'suspended']);

        $this->withHeaders(['X-Client-Id' => 'pk_live_test123'])
            ->getJson('/api/v1/vendor/public/products')
            ->assertStatus(403);
    }
}
