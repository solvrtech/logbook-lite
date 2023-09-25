<?php

namespace App\Repository\Alert;

use App\Entity\AlertSetting;
use App\Entity\User;
use App\Model\Pagination;
use App\Model\Request\SearchRequest;

interface AlertSettingRepositoryInterface
{
    /**
     * Find all alerts matching the search request.
     *
     * @param User $user
     * @param int $appId
     * @param SearchRequest $searchRequest
     *
     * @return Pagination
     */
    public function findAlert(User $user, int $appId, SearchRequest $searchRequest): Pagination;

    /**
     * Find app alert matching with the given $alertId.
     *
     * @param int $appId
     * @param string $source
     *
     * @return array
     */
    public function findAppAlert(int $appId, string $source): array;

    /**
     * Find app alert matching with the given $alertId.
     *
     * @param int $appId
     * @param int $alertId
     * @param ?User $user
     *
     * @return AlertSetting|null
     */
    public function findAppAlertById(int $appId, int $alertId, ?User $user): AlertSetting|null;

    /**
     * Get all alerts from storage.
     *
     * @return array
     */
    public function getAll(): array;

    /**
     * Save AlertSetting entity into storage.
     *
     * @param AlertSetting $alert
     */
    public function save(AlertSetting $alert): void;

    /**
     * Delete AlertSetting entity from the storage.
     *
     * @param AlertSetting $alert
     */
    public function delete(AlertSetting $alert): void;
}