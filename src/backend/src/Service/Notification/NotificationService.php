<?php

namespace App\Service\Notification;

use App\Common\Config\AlertConfig;
use App\Entity\Notification;
use App\Entity\UserNotification;
use App\Model\Pagination;
use App\Model\Request\NotificationDeleteRequest;
use App\Model\Request\NotificationRequest;
use App\Model\Request\SearchRequest;
use App\Model\Response\MailSettingResponse;
use App\Repository\Notification\NotificationRepositoryInterface;
use App\Repository\Notification\UserNotificationRepositoryInterface;
use App\Service\BaseService;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class NotificationService
    extends BaseService
    implements NotificationServiceInterface
{
    private NotificationRepositoryInterface $notificationRepository;
    private UserNotificationRepositoryInterface $userNotificationRepository;

    public function __construct(
        NotificationRepositoryInterface $notificationRepository,
        UserNotificationRepositoryInterface $userNotificationRepository,
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->userNotificationRepository = $userNotificationRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function searchNotification(SearchRequest $searchRequest): Pagination
    {
        // validate request
        $this->validate($searchRequest);

        return $this->notificationRepository
            ->findNotification($this->getUser(), $searchRequest);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function getNotificationById(int $notificationId): Notification
    {
        $notification = $this->userNotificationRepository
            ->findUserNotificationId($this->getUser(), $notificationId);

        if (null === $notification) {
            throw new Exception("Notification not found");
        }

        return $notification->getNotification();
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function send(Notification $notification, TemplatedEmail $templatedEmail, array $users): void
    {
        $channels = (array)$this->getParam('alert_channel');

        if (in_array(AlertConfig::EMAIL, $channels, true)) {
            $mailSetting = $this->mailSetting()->getMailSettingCached();

            if ($mailSetting) {
                self::initEmailChannel(
                    $templatedEmail,
                    $mailSetting,
                    $users
                );
            }
        }
    }

    /**
     * Send notification via email.
     *
     * @param TemplatedEmail $templatedEmail
     * @param MailSettingResponse $mailSetting
     * @param array $users
     */
    private function initEmailChannel(
        TemplatedEmail $templatedEmail,
        MailSettingResponse $mailSetting,
        array $users
    ): void {
        $i = 0;
        foreach ($users as $user) {
            if (0 === $i) {
                $templatedEmail->to($user->getEmail());
            } else {
                $templatedEmail->addTo($user->getEmail());
            }

            $i++;
        }

        try {
            $this->sendEmail(
                $mailSetting,
                $templatedEmail
            );
        } catch (Exception $exception) {
            $this->log()->error($exception);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function save(NotificationRequest $notificationRequest): Notification
    {
        $notification = (new Notification())
            ->setApp($notificationRequest->getApp())
            ->setMessage($notificationRequest->getMessage())
            ->setLink($notificationRequest->getLink())
            ->setCreatedAt($notificationRequest->getCreatedAt());

        foreach ($notificationRequest->getUsers() as $user) {
            $notification->addUserNotification(
                (new UserNotification())->setUser($user)
            );
        }

        try {
            $this->notificationRepository->save($notification);
        } catch (Exception $exception) {
            throw new Exception("Save notification was failed");
        }

        return $notification;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function bulkDelete(NotificationDeleteRequest $request): void
    {
        // validate request
        $this->validate($request);

        try {
            $this->userNotificationRepository
                ->bulkDelete($this->getUser(), $request->getIds());
        } catch (Exception $exception) {
            throw new Exception("Bulk delete notifications was failed");
        }

    }

    /**
     * {@inhertiDoc}
     *
     * @throws Exception
     */
    public function deleteAll(): void
    {
        try {
            $this->userNotificationRepository->deleteAll($this->getUser());
        } catch (Exception $exception) {
            throw new Exception("Delete all notifications was failed");
        }
    }

    /**
     * {@inhertiDoc}
     *
     * @throws Exception
     */
    public function delete(int $notificationId): void
    {
        $notification = $this->userNotificationRepository
            ->findUserNotificationId($this->getUser(), $notificationId);

        if (null === $notification) {
            throw new Exception("Delete notification was failed");
        }

        try {
            $this->userNotificationRepository->delete($notification);
        } catch (Exception $exception) {
            throw new Exception("Delete notification was failed");
        }
    }
}