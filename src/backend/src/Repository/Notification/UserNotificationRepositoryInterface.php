<?php

namespace App\Repository\Notification;

use App\Entity\User;
use App\Entity\UserNotification;

interface UserNotificationRepositoryInterface
{
    /**
     * Find UserNotification entity matching with the given $id.
     *
     * @param User $user
     * @param int $notificationId
     *
     * @return UserNotification|null
     */
    public function findUserNotificationId(User $user, int $notificationId): UserNotification|null;

    /**
     * Delete UserNotification entity from the storage.
     *
     * @param UserNotification $userNotification
     */
    public function delete(UserNotification $userNotification): void;

    /**
     * Bulk delete notification of the user from storage.
     *
     * @param User $user
     * @param array $ids
     */
    public function bulkDelete(User $user, array $ids): void;

    /**
     * Delete all notifications of the user from storage.
     *
     * @param User $user
     */
    public function deleteAll(User $user): void;
}