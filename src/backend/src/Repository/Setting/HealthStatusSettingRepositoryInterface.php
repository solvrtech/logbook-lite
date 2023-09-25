<?php

namespace App\Repository\Setting;

use App\Entity\HealthStatusSetting;

interface HealthStatusSettingRepositoryInterface
{
    /**
     * Retrieve app health setting from storage.
     *
     * @param int $appId
     *
     * @return HealthStatusSetting|null
     */
    public function findAppHealthStatus(int $appId): HealthStatusSetting|null;

    /**
     * Retrieve all the health settings of app are enable to check from storage.
     *
     * @return array
     */
    public function getAllHealthSettings(): array;

    /**
     * Retrieve health check period of app from storage.
     *
     * @param int $appId
     *
     * @return int
     */
    public function findPeriodHealthCheckByAppId(int $appId): int;

    /**
     * Save HealthStatusSetting entity into storage.
     *
     * @param HealthStatusSetting $healthStatusSetting
     */
    public function save(HealthStatusSetting $healthStatusSetting): void;
}