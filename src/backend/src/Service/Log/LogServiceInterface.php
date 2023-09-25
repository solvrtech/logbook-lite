<?php

namespace App\Service\Log;

use App\Model\Pagination;
use App\Model\Request\LogBatchRequest;
use App\Model\Request\LogRequest;
use App\Model\Request\LogSearchRequest;

interface LogServiceInterface
{
    /**
     * Search logs matching the given $request from storage.
     *
     * @param LogSearchRequest $request
     *
     * @return Pagination
     */
    public function searchLog(LogSearchRequest $request): Pagination;

    /**
     * Retrieve a log matching the given $logId from storage.
     *
     * @param int $logId
     *
     * @return array
     */
    public function getLogById(int $logId): array;

    /**
     * Retrieve appId of log matching the given $logId and $assignee from storage.
     *
     * @param int $logId
     * @param int $assignee
     *
     * @return array
     */
    public function getAppIdByLogIdAndAssignee(int $logId, int $assignee): array;

    /**
     * Search app logs matching the given $appId and $request from storage.
     *
     * @param int $appId
     * @param LogSearchRequest $request
     *
     * @return Pagination
     */
    public function searchAppLog(int $appId, LogSearchRequest $request): Pagination;

    /**
     * Retrieve log matching the given $appId and $logId from storage.
     *
     * @param int $appId
     * @param int $logId
     *
     * @return array
     */
    public function getLogByAppIdAndLogId(int $appId, int $logId): array;

    /**
     * Saving new Log into storage.
     *
     * @param LogRequest $request
     */
    public function create(LogRequest $request): void;
}