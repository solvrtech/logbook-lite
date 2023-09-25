<?php

namespace App\Repository\Health;

use App\Entity\HealthStatus;
use App\Entity\User;
use App\Model\Pagination;
use App\Model\Request\HealthStatusSearchRequest;

interface HealthStatusRepositoryInterface
{
    /**
     * Find all health status of app matching with the search request.
     *
     * @param User $user
     * @param HealthStatusSearchRequest $searchRequest
     * @param ?int $appId
     *
     * @return Pagination
     */
    public function findHealthStatus(User $user, HealthStatusSearchRequest $searchRequest, ?int $appId = null):
    Pagination;

    /**
     * Find health status of app matching with $appId and $healthStatusId.
     *
     * @param int $appId
     * @param int $healthStatusId
     *
     * @return HealthStatus|null
     */
    public function findAppHealthStatusById(int $appId, int $healthStatusId): HealthStatus|null;

    /**
     * Find app id of health status matching with the given $healthStatusId.
     *
     * @param int $healthStatusId
     *
     * @return int|null
     */
    public function findAppIdByHealthStatusId(int $healthStatusId): int|null;

    /**
     * Checking the application health status for today has a failed status of more than the limit
     *
     * @param int $appId
     * @param int $limit
     *
     * @return array
     */
    public function appHealthHasFailedStatus(int $appId, int $limit): array;

    /**
     * Find health status issue of app matching with $appId and specific alert config.
     *
     * @param int $appId
     * @param array $alertConfig
     *
     * @return bool
     */
    public function appHealthHasIssue(int $appId, array $alertConfig): bool;

    /**
     * Save health status into storage.
     *
     * @param HealthStatus $healthStatus
     */
    public function save(HealthStatus $healthStatus): void;
}
