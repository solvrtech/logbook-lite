<?php

namespace App\Controller;

use App\Common\Config\UserConfig;
use App\Model\Request\NotificationDeleteRequest;
use App\Model\Request\SearchRequest;
use App\Model\Response\Response;
use App\Security\Authorization\AuthorizationCheckerInterface;
use App\Service\Notification\NotificationServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends BaseController
{
    private NotificationServiceInterface $notificationService;

    public function __construct(
        NotificationServiceInterface $notificationService,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->notificationService = $notificationService;

        parent::__construct($authorizationChecker);
    }

    /**
     * Handle an incoming get all notifications request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/api/notification', methods: ['GET'])]
    public function getAll(Request $request): JsonResponse
    {
        // access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD]
        );

        $searchRequest = $this->serialize(
            $request->query->all(),
            SearchRequest::class
        );

        return $this->json(
            new Response(
                true,
                "Get all notification",
                $this->notificationService->searchNotification($searchRequest)
            )
        );
    }

    /**
     * Handle an incoming fetch notification request.
     *
     * @param int $notificationId
     *
     * @return JsonResponse
     */
    #[Route('/api/notification/{notificationId}', methods: ['GET'])]
    public function getNotification(int $notificationId): JsonResponse
    {
        // access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD]
        );

        return $this->json(
            new Response(
                true,
                "Get notification",
                $this->notificationService
                    ->getNotificationById($notificationId)
                    ->toResponse()
            )
        );
    }

    /**
     * Handle an incoming delete notification request.
     *
     * @Param int $notificationId
     *
     * @return JsonResponse
     */
    #[Route('/api/notification/{notificationId}/delete', methods: ['DELETE'])]
    public function delete(int $notificationId): JsonResponse
    {
        // access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD]
        );

        $this->notificationService->delete($notificationId);

        return $this->json(
            new Response(
                true,
                "Delete notification"
            )
        );
    }

    /**
     * Handle an incoming bulk delete notifications request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/api/notification/bulk-delete', methods: ['POST'])]
    public function bulkDelete(Request $request): JsonResponse
    {
        // access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD]
        );

        $deleteRequest = $this->serialize(
            $request->getContent(),
            NotificationDeleteRequest::class
        );
        $this->notificationService->bulkDelete($deleteRequest);

        return $this->json(
            new Response(
                true,
                "Bulk delete notifications"
            )
        );
    }

    /**
     * Handle an incoming delete all notifications request.
     *
     * @return JsonResponse
     */
    #[Route('/api/notification/delete-all', methods: ['DELETE'])]
    public function deleteAll(): JsonResponse
    {
        // access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD]
        );

        $this->notificationService->deleteAll();

        return $this->json(
            new Response(
                true,
                "Delete all notifications"
            )
        );
    }
}