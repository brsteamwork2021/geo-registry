<?php

namespace Brsteamwork2021\GeoRegistry\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Brsteamwork2021\GeoRegistry\Services\GeoRegistryService;

class FetchGeoRegistryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
        $this->onConnection('sync');
    }

    public function handle(GeoRegistryService $ipInfoService)
    {
        try {
            $ipInfo = $ipInfoService->getIpInfo($this->model->ip_address);

            if ($ipInfo) {
                $this->model->update([

                    'country' => $ipInfoService->getCountryCode($ipInfo) ?? 'Unknown',
                    'language' => $ipInfoService->getLanguageCode($ipInfo) ?? 'Unknown',
                    'is_proxy' => $ipInfoService->isProxy($ipInfo) || $ipInfoService->isVpn($ipInfo) ?? false,
                    
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch IP info: ' . $e->getMessage());
        }
    }
}