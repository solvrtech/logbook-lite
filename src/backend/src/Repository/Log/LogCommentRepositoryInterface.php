<?php

namespace App\Repository\Log;

use App\Entity\LogComment;
use App\Entity\User;

interface LogCommentRepositoryInterface
{
    /**
     * Find all comments of log matching the given $logId and $appId.
     *
     * @param User $user
     * @param int|string $logId
     * @param int $appId
     *
     * @return array
     */
    public function findComment(User $user, int|string $logId, int $appId): array;

    /**
     * Find LogComment entity matching with the given $commentId and $userId.
     *
     * @param int $commentId
     * @param int $userId
     *
     * @return LogComment|null
     */
    public function findLogCommentById(int $commentId, int $userId): LogComment|null;

    /**
     * Save LogComment entity into storage.
     *
     * @param LogComment $logComment
     */
    public function save(LogComment $logComment): void;

    /**
     * Delete LogComment entity from the storage.
     *
     * @param LogComment $logComment
     */
    public function delete(LogComment $logComment): void;
}