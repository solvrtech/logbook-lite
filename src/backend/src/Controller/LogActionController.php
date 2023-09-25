<?php

namespace App\Controller;

use App\Common\Config\TeamConfig;
use App\Common\Config\UserConfig;
use App\Model\Request\LogActionRequest;
use App\Model\Response\Response;
use App\Security\Authorization\AuthorizationCheckerInterface;
use App\Security\Authorization\LogAuthorization;
use App\Service\Log\LogActionServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LogActionController extends BaseController
{
    private LogActionServiceInterface $logActionService;

    public function __construct(
        LogActionServiceInterface $logActionService,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->logActionService = $logActionService;

        parent::__construct($authorizationChecker);
    }

    /**
     * Handle an incoming update priority of log request.
     *
     * @param int $logId
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/api/log/{logId}/action-priority', methods: ['POST'])]
    public function updatePriority(int $logId, Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD],
            (new LogAuthorization())
                ->setId($logId)
                ->setRequiredRole([
                    TeamConfig::TEAM_MANAGER,
                ])
        );

        $logActionRequest = $this->serialize(
            $request->getContent(),
            LogActionRequest::class
        );

        return $this->json(
            new Response(
                true,
                "Update log priority",
                $this->logActionService->updatePriority(
                    $logId,
                    $logActionRequest
                )
            )
        );
    }

    /**
     * Handle an incoming update status of log request.
     *
     * @param int $logId
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/api/log/{logId}/action-status', methods: ['POST'])]
    public function updateStatus(int $logId, Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD],
            (new LogAuthorization())
                ->setId($logId)
                ->setRequiredRole([
                    TeamConfig::TEAM_MANAGER,
                ])
        );

        $logActionRequest = $this->serialize(
            $request->getContent(),
            LogActionRequest::class
        );

        return $this->json(
            new Response(
                true,
                "Update log status",
                $this->logActionService->updateStatus(
                    $logId,
                    $logActionRequest
                )
            )
        );
    }

    /**
     * Handle an incoming update assignee of log request.
     *
     * @param int $logId
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/api/log/{logId}/action-assignee', methods: ['POST'])]
    public function updateAssignee(int $logId, Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD],
            (new LogAuthorization())
                ->setId($logId)
                ->setRequiredRole([
                    TeamConfig::TEAM_MANAGER,
                ])
        );

        $logActionRequest = $this->serialize(
            $request->getContent(),
            LogActionRequest::class
        );

        return $this->json(
            new Response(
                true,
                "Update log assignee",
                $this->logActionService->updateAssignee(
                    $logId,
                    $logActionRequest
                )
            )
        );
    }

    /**
     * Handle an incoming update tag of log request.
     *
     * @param int $logId
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/api/log/{logId}/action-tag', methods: ['POST'])]
    public function updateTag(int $logId, Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD],
            (new LogAuthorization())
                ->setId($logId)
                ->setRequiredRole([
                    TeamConfig::TEAM_MANAGER,
                ])
        );

        $logActionRequest = $this->serialize(
            $request->getContent(),
            LogActionRequest::class
        );

        return $this->json(
            new Response(
                true,
                "Update log tag",
                $this->logActionService->updateTag(
                    $logId,
                    $logActionRequest
                )
            )
        );
    }
}