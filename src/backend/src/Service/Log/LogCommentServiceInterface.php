<?php

namespace App\Service\Log;

use App\Model\Request\LogCommentRequest;

interface LogCommentServiceInterface
{
    /**
     * Retrieve all comments of log matching the given $logId and $appId from storage.
     *
     * @param int|string $logId
     * @param int $appId
     *
     * @return array
     */
    public function getAllCommentByLogId(int|string $logId, int $appId): array;

    /**
     * Saving new log comment into storage.
     *
     * @param int $logId
     * @param LogCommentRequest $request
     */
    public function create(int $logId, LogCommentRequest $request): array;

    /**
     * Update comment of log matching the given $commentId.
     *
     * @param int $commentId
     * @param LogCommentRequest $request
     *
     * @return array
     */
    public function update(int $commentId, LogCommentRequest $request): array;

    /**
     * Remove LogComment matching the given $commentId from storage.
     *
     * @param int $commentId
     */
    public function delete(int $commentId): void;
}