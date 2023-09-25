<?php

namespace App\Repository\Log;

use App\Entity\SqlLog;
use App\Entity\User;
use App\Model\Pagination;
use App\Model\Request\LogSearchRequest;

interface LogRepositoryInterface
{
    /**
     * Find all logs matching the search request.
     *
     * @param User $user
     * @param LogSearchRequest $searchRequest
     * @param int|null $appId
     *
     * @return Pagination
     */
    public function findLog(User $user, LogSearchRequest $searchRequest, int|null $appId = null): Pagination;

    /**
     * Find app log matching with the given $appId and $logId.
     *
     * @param int $appId
     * @param int $logId
     *
     * @return SqlLog|null
     */
    public function findLogByAppIdAndLogId(int $appId, int $logId): SqlLog|null;

    /**
     * Find app log matching with the given alert configuration.
     *
     * @param int $appId
     * @param array $alertConfig
     *
     * @return ?SqlLog
     */
    public function shouldSendNotification(int $appId, array $alertConfig): ?SqlLog;

    /**
     * Find app id of log matching with the given $logId and $assignee.
     *
     * @param int $logId
     * @param int $assignee
     *
     * @return array
     */
    public function findAppIdByLogIdAndAssignee(int $logId, int $assignee): array;

    /**
     * Save Log entity into storage.
     *
     * @param SqlLog $log
     */
    public function save(SqlLog $log): void;

    /**
     * Delete app log from the storage matching the given $appId.
     *
     * @param int $appId
     */
    public function deleteByAppId(int $appId): void;
}