<?php

namespace App\Repository\Notification;

use App\Entity\Notification;
use App\Entity\User;
use App\Model\Pagination;
use App\Model\Request\SearchRequest;

interface NotificationRepositoryInterface
{
    /**
     * Find notification matching with given user and search request.
     *
     * @param User $user
     * @param SearchRequest $request
     *
     * @return Pagination
     */
    public function findNotification(User $user, SearchRequest $request): Pagination;

    /**
     * Save Notification entity into storage.
     *
     * @param Notification $notification
     */
    public function save(Notification $notification): void;
}