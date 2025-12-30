<?php

namespace App\Services;

class CustomerService extends BaseExternalService
{
    protected function getResourceName(): string
    {
        return 'admin/customers';
    }

    /**
     * Format customer data for API according to openapi.yaml UserDTO schema
     * UserDTO has: { id: int64, name: string, email: string, phoneNumber: string, password: string }
     */
    protected function formatPayload(array $data): array
    {
        $payload = [
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
        ];

        // Map phone_number to phoneNumber (camelCase for API)
        if (isset($data['phone_number'])) {
            $payload['phoneNumber'] = $data['phone_number'];
        } elseif (isset($data['phoneNumber'])) {
            $payload['phoneNumber'] = $data['phoneNumber'];
        }

        // Password is optional for updates, required for creates
        if (isset($data['password'])) {
            $payload['password'] = $data['password'];
        }

        return $payload;
    }
}

