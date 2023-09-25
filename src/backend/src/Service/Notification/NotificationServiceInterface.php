<?php

namespace App\Service\Notification;

use App\Entity\Notification;
use App\Model\Pagination;
use App\Model\Request\NotificationDeleteRequest;
use App\Model\Request\NotificationRequest;
use App\Model\Request\SearchRequest;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

interface NotificationServiceInterface
{
    /**
     * Search notification matching the given $request from storage.
     *
     * @param SearchRequest $searchRequest
     *
     * @return array
     */
    public function searchNotification(SearchRequest $searchRequest): Pagination;

    /**
     * Retrieve notification matching with the given notificationId.
     *
     * @param int $notificationId
     *
     * @return Notification
     */
    public function getNotificationById(int $notificationId): Notification;

    /**
     * Send notification to the designated users.
     *
     * @param Notification $notification
     * @param TemplatedEmail $templatedEmail
     * @param array $users
     *
     */
    public function send(Notification $notification, TemplatedEmail $templatedEmail, array $users): void;

    /**
     * Save notification into storage.
     *
     * @param NotificationRequest $notificationRequest
     *
     * @return Notification
     */
    public function save(NotificationRequest $notificationRequest): Notification;

    /**
     * Remove user notification matching the given $id from storage.
     *
     * @param int $notificationId
     */
    public function delete(int $notificationId): void;

    /**
     * Remove user notifications matching with the given delete request object.
     *
     * @param NotificationDeleteRequest $request
     */
    public function bulkDelete(NotificationDeleteRequest $request): void;

    /**
     * Remove all notifications of the user from storage.
     */
    public function deleteAll(): void;
}