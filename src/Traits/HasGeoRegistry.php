<?php

namespace YourVendor\GeoRegistry\Traits;

use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Facades\Agent;
use Illuminate\Support\Facades\Request;
use YourVendor\GeoRegistry\Jobs\FetchGeoRegistryJob;
use Illuminate\Support\Str;

trait HasGeoRegistry
{
    public static function bootHasGeoRegistry(): void
    {
        static::creating(function ($model) {
            $model->syncGeo();
        });

        static::created(function ($model) {
            FetchGeoRegistryJob::dispatch($model);
        });
    }

    protected function syncGeo(): void
    {
        try {
            $device = $this->getDevice();
            $language = $this->getLanguage();
            $this->fill([
                'ip_address' => $this->getIpAddress(),
                'browser' => strtolower(str_replace(' ', '_', Agent::browser())),
                'device' => $device,
                'device_os' => strtolower(str_replace(' ', '_', Agent::platform())),
                'is_robot' => Agent::isRobot(),
                'browser_language' => $language,
                'device_fingerprint' => $this->generateDeviceId(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to sync device source: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function getDevice(): string
    {
        return match(true) {
            Agent::isDesktop() => 'desktop',
            Agent::isPhone() => 'phone',
            Agent::isTablet() => 'tablet',
            default => 'unknown',
        };
    }

    protected function getLanguage(): ?string
    {
        $languages = Agent::languages();
        return !empty($languages) ? explode('-', $languages[0])[0] : null;
    }

    protected function getIpAddress(): string
    {
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                return $_SERVER[$header];
            }
        }
        
        return request()->ip() ?? '127.0.0.1';
    }

    protected function generateDeviceId(): string
    {
        $data = [
            'user_agent' => Request::header('User-Agent'),
            'accept_language' => Request::header('Accept-Language'),
            'screen_resolution' => Request::header('X-Screen-Resolution') ?? 'unknown',
            'color_depth' => Request::header('X-Color-Depth') ?? 'unknown',
            'timezone' => Request::header('X-Timezone') ?? 'unknown',
            'installed_fonts' => Request::header('X-Installed-Fonts') ?? 'unknown',
            'installed_plugins' => Request::header('X-Installed-Plugins') ?? 'unknown',
            'platform' => Agent::platform(),
            'browser' => Agent::browser(),
            'device' => $this->getDevice(),
        ];
        $deviceId = hash('sha256', json_encode($data));
        return Str::substr($deviceId, 0, 32);
    }

    public static function isDeviceAlreadyRegistered(string $deviceId): bool
    {
        return static::where('device_id', $deviceId)->exists();
    }
}
