<?php

namespace App\Controller;

use App\Common\Config\TeamConfig;
use App\Common\Config\UserConfig;
use App\Model\Request\LogCommentRequest;
use App\Model\Response\Response;
use App\Security\Authorization\AuthorizationCheckerInterface;
use App\Security\Authorization\LogAuthorization;
use App\Service\Log\LogCommentServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LogCommentController extends BaseController
{
    private LogCommentServiceInterface $logCommentService;

    public function __construct(
        LogCommentServiceInterface $logCommentService,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->logCommentService = $logCommentService;

        parent::__construct($authorizationChecker);
    }

    /**
     * Handle an incoming save new comment of log request.
     *
     * @param int $logId
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/api/log/{logId}/comment', methods: ['POST'])]
    public function create(int $logId, Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD],
            (new LogAuthorization())
                ->setId($logId)
                ->setRequiredRole([
                    TeamConfig::TEAM_MANAGER,
                    TeamConfig::TEAM_STANDARD,
                ])
        );

        $logCommentRequest = $this->serialize(
            $request->getContent(),
            LogCommentRequest::class
        );

        return $this->json(
            new Response(
                true,
                "Save new log comment",
                $this->logCommentService->create(
                    $logId,
                    $logCommentRequest
                )
            )
        );
    }

    /**
     * Handle an incoming update comment of log request.
     *
     * @param int|string $logId
     * @param int $commentId
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/api/log/{logId}/comment/{commentId}/edit', methods: ['PUT'])]
    public function edit(int|string $logId, int $commentId, Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD],
            (new LogAuthorization())
                ->setId($logId)
                ->setRequiredRole([
                    TeamConfig::TEAM_MANAGER,
                    TeamConfig::TEAM_STANDARD,
                ])
        );

        $logCommentRequest = $this->serialize(
            $request->getContent(),
            LogCommentRequest::class
        );

        return $this->json(
            new Response(
                true,
                "Update log comment",
                $this->logCommentService->update(
                    $commentId,
                    $logCommentRequest
                )
            )
        );
    }

    /**
     * Handle an incoming delete comment of log request.
     *
     * @param int|string $logId
     * @param int $commentId
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/api/log/{logId}/comment/{commentId}/delete', methods: ['DELETE'])]
    public function delete(int|string $logId, int $commentId, Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD],
            (new LogAuthorization())
                ->setId($logId)
                ->setRequiredRole([
                    TeamConfig::TEAM_MANAGER,
                    TeamConfig::TEAM_STANDARD,
                ])
        );

        $this->logCommentService->delete($commentId);

        return $this->json(
            new Response(
                true,
                "Delete log comment",
            )
        );
    }
}