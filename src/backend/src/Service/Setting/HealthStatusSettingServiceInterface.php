<?php

namespace App\Service\Setting;

use App\Model\Request\HealthStatusSettingRequest;

interface HealthStatusSettingServiceInterface
{

    /**
     * Get all the health settings of app that need to be checked from cache.
     * 
     * @return array
     */
    public function getAllHealthSettingsCached(): array;

    /**
     * Saving app health setting into storage.
     *
     * @param HealthStatusSettingRequest $request
     * @param int $appId
     */
    public function save(HealthStatusSettingRequest $request, int $appId): void;
}
