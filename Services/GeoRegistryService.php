<?php

namespace Brsteamwork2021\GeoRegistry\Services;

use Illuminate\Support\Facades\Http;

class GeoRegistryService
{
    private $apiKey;
    private $baseUrl = 'https://api.ipregistry.co/';
    private $timeout = 8;

    public function __construct()
    {
        $this->apiKey = 'ow498c91mrf1j5wl';
    }


    public function getIpInfo(string $ip)
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl . $ip, [
                'key' => $this->apiKey
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('IP info request failed: ' . $e->getMessage());
        }

        return null;
    }


    public function getCountryCode(array $ipInfo): ?string
    {
        return $ipInfo['location']['country']['code'] ?? null;
    }

    public function getLanguageCode(array $ipInfo): ?string
    {
        return $ipInfo['location']['language']['code'] ?? null;
    }

    public function isProxy(array $ipInfo): bool
    {
        return $ipInfo['security']['is_proxy'] ?? false;
    }

    public function isVpn(array $ipInfo): bool
    {
        return $ipInfo['security']['is_vpn'] ?? false;
    }
}