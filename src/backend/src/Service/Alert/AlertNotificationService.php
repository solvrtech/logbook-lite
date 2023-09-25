<?php

namespace App\Service\Alert;

use App\Entity\App;
use App\Model\AlertNotification;
use App\Model\Request\NotificationRequest;
use App\Repository\App\AppRepositoryInterface;
use App\Repository\User\UserRepositoryInterface;
use App\Service\BaseService;
use App\Service\Notification\NotificationServiceInterface;
use App\Service\Setting\AlertSettingServiceInterface;
use DateTime;
use Exception;

class AlertNotificationService
    extends BaseService
    implements AlertNotificationServiceInterface
{
    private UserRepositoryInterface $userRepository;
    private AppRepositoryInterface $appRepository;
    private NotificationServiceInterface $notificationService;
    private AlertSettingServiceInterface $alertSettingService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        AppRepositoryInterface $appRepository,
        NotificationServiceInterface $notificationService,
        AlertSettingServiceInterface $alertSettingService
    ) {
        $this->userRepository = $userRepository;
        $this->appRepository = $appRepository;
        $this->notificationService = $notificationService;
        $this->alertSettingService = $alertSettingService;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function sendNotification(AlertNotification $alertNotification): void
    {
        $users = $this->getAllRecipients(
            $alertNotification->getAppId(),
            $alertNotification->getNotifyTo()
        );

        if (null === $users) {
            throw new Exception("Recipients were not found");
        }

        $dateTime = new DateTime();
        $notificationRequest = (new NotificationRequest())
            ->setApp(self::getApp($alertNotification->getAppId()))
            ->setMessage($alertNotification->getMessage())
            ->setLink($alertNotification->getUrl())
            ->setUsers($users)
            ->setCreatedAt($dateTime);
        $this->notificationService
            ->send(
                $this->notificationService->save($notificationRequest),
                $alertNotification->getEmailTemplate(),
                $users
            );

        // set the notified date of the alert.
        $this->alertSettingService->updateLastNotified(
            $alertNotification->getAppId(),
            $alertNotification->getAlertId(),
            $dateTime
        );
    }

    /**
     * Get all users who will receive the notification.
     *
     * @param int $appId
     * @param string $role
     *
     * @return array|null
     */
    private function getAllRecipients(int $appId, string $role): array|null
    {
        try {
            return $this->userRepository->findUserAssignedToApp(
                $appId,
                $role
            );
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * Get an app from the given appId
     *
     * @param int $appId
     *
     * @return App
     *
     * @throws Exception
     */
    private function getApp(int $appId): App
    {
        $app = $this->appRepository->findAppById($appId);

        if (null === $app) {
            throw new Exception("App not found");
        }

        return $app;
    }
}