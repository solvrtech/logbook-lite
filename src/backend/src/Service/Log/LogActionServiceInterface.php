<?php

namespace App\Service\Log;

use App\Model\Request\LogActionRequest;

interface LogActionServiceInterface
{
    /**
     * Update log status matching the given $logId.
     *
     * @param int|string $logId
     * @param LogActionRequest $request
     *
     * @return array
     */
    public function updateStatus(int|string $logId, LogActionRequest $request): array;

    /**
     * Update log priority matching the given $logId.
     *
     * @param int|string $logId
     * @param LogActionRequest $request
     *
     * @return array
     */
    public function updatePriority(int|string $logId, LogActionRequest $request): array;

    /**
     * Update log assignee matching the given $logId.
     *
     * @param int|string $logId
     * @param LogActionRequest $request
     *
     * @return array
     */
    public function updateAssignee(int|string $logId, LogActionRequest $request): array;

    /**
     * Update log tags matching the given $logId.
     *
     * @param int|string $logId
     * @param LogActionRequest $request
     *
     * @return array
     */
    public function updateTag(int|string $logId, LogActionRequest $request): array;
}