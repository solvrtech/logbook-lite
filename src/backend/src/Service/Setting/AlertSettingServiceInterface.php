<?php

namespace App\Service\Setting;

use App\Entity\AlertSetting;
use App\Model\Pagination;
use App\Model\Request\AlertSettingRequest;
use App\Model\Request\SearchRequest;
use DateTime;

interface AlertSettingServiceInterface
{
    /**
     * Search app alerts matching the given $appId and $request from storage.
     *
     * @param int $appId
     * @param SearchRequest $request
     *
     * @return Pagination
     */
    public function searchAppAlert(int $appId, SearchRequest $request): Pagination;

    /**
     * Retrieve an app alert matching the given $appId and $alertId from storage.
     *
     * @param int $appId
     * @param int $alertId
     *
     * @return AlertSetting
     */
    public function getAppAlert(int $appId, int $alertId): AlertSetting;

    /**
     * Retrieve all app alerts matching with the given source from cache.
     *
     * @param int $appId
     * @param string $source
     *
     * @return array;
     */
    public function getCachedAppAlertSettings(int $appId, string $source): array;

    /**
     * Saving new AlertSetting into storage.
     *
     * @param AlertSettingRequest $request
     * @param int $appId
     */
    public function create(AlertSettingRequest $request, int $appId): void;

    /**
     * Update app alert matching the given $appId and $alertId.
     *
     * @param AlertSettingRequest $request
     * @param int $appId
     * @param int $alertId
     */
    public function update(AlertSettingRequest $request, int $appId, int $alertId): void;

    /**
     * Update the last notified of the alert.
     *
     * @param int $appId
     * @param int $alertId
     * @param DateTime $lastNotified
     */
    public function updateLastNotified(int $appId, int $alertId, DateTime $lastNotified): void;

    /**
     * Remove app alert matching the given $appId and $alertId from storage.
     *
     * @param int $appId
     * @param int $alertId
     */
    public function delete(int $appId, int $alertId): void;
}