<?php

namespace App\Service\Health;

use App\Entity\HealthStatus;
use App\Model\Pagination;
use App\Model\Request\HealthStatusRequest;
use App\Model\Request\HealthStatusSearchRequest;

interface HealthStatusServiceInterface
{
    /**
     * Search app health status matching the given $appId and $request from storage.
     *
     * @param HealthStatusSearchRequest $request
     * @param ?int $appId
     *
     * @return Pagination
     */
    public function search(HealthStatusSearchRequest $request, ?int $appId = null): Pagination;

    /**
     * Retrieve health status matching the given $healthStatusId from storage.
     *
     * @param int $healthStatusId
     *
     * @return HealthStatus
     */
    public function getHealthStatus(int $healthStatusId): HealthStatus;

    /**
     * Retrieve health status matching withs given $appId and $healthStatusId.
     *
     * @param int $appId
     * @param int $healthStatusId
     *
     * @return HealthStatus
     */
    public function getAppHealthStatus(int $appId, int $healthStatusId): HealthStatus;

    /**
     * Retrieve all schedules of health status checkup.
     *
     * @return array
     */
    public function getAllSchedules(): array;

    /**
     * Get app id of health status matching with the given healthStatusId.
     *
     * @param int $healthStatusId
     *
     * @return int|null
     */
    public function getAppIdByHealthStatusId(int $healthStatusId): int|null;

    /**
     * Update schedules of health status checkup.
     *
     * @param array $schedules
     */
    public function scheduleUpdate(array $schedules): void;

    /**
     * Save new Health Status into storage.
     *
     * @param int $appId
     * @param HealthStatusRequest|null $request
     */
    public function create(int $appId, HealthStatusRequest|null $request = null): void;
}
