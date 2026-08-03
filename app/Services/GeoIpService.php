<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoIpService
{
    /**
     * Resolve an IP address to geographic information.
     *
     * @return array{country: string|null, city: string|null, timezone: string|null, isp: string|null}
     */
    public function lookup(?string $ipAddress): array
    {
        $empty = [
            'country' => null,
            'city' => null,
            'timezone' => null,
            'isp' => null,
        ];

        if (!config('audit.geoip.enabled', true)) {
            return $empty;
        }

        if (empty($ipAddress) || $this->isPrivateIp($ipAddress)) {
            return $empty;
        }

        $cacheKey = 'geoip:' . $ipAddress;
        $cacheTtl = config('audit.geoip.cache_ttl', 86400);

        return Cache::remember($cacheKey, $cacheTtl, function () use ($ipAddress, $empty) {
            try {
                $apiUrl = config('audit.geoip.api_url', 'http://ip-api.com/json/');
                $timeout = config('audit.geoip.timeout', 3);

                $response = Http::timeout($timeout)
                    ->get($apiUrl . $ipAddress, [
                        'fields' => 'status,country,city,timezone,isp',
                    ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (($data['status'] ?? '') === 'success') {
                        return [
                            'country' => $data['country'] ?? null,
                            'city' => $data['city'] ?? null,
                            'timezone' => $data['timezone'] ?? null,
                            'isp' => $data['isp'] ?? null,
                        ];
                    }
                }

                return $empty;
            } catch (\Throwable $e) {
                Log::warning('GeoIP lookup failed', [
                    'ip' => $ipAddress,
                    'error' => $e->getMessage(),
                ]);

                return $empty;
            }
        });
    }

    /**
     * Check if an IP address is private/reserved (not routable).
     */
    protected function isPrivateIp(string $ip): bool
    {
        return !filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }
}
