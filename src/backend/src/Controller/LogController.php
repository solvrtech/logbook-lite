<?php

namespace App\Controller;

use App\Common\Config\TeamConfig;
use App\Common\Config\UserConfig;
use App\Model\Request\LogRequest;
use App\Model\Request\LogSearchRequest;
use App\Model\Response\Response;
use App\Security\Authorization\AppAuthorization;
use App\Security\Authorization\AuthorizationCheckerInterface;
use App\Security\Authorization\LogAuthorization;
use App\Service\Log\LogServiceInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LogController extends BaseController
{
    private LogServiceInterface $logService;

    public function __construct(
        LogServiceInterface $logService,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->logService = $logService;

        parent::__construct($authorizationChecker);
    }

    /**
     * Handle an incoming search log request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/log', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD]
        );

        $searchRequest = $this->serialize(
            $request->query->all(),
            LogSearchRequest::class
        );

        return $this->json(
            new Response(
                true,
                "Search log",
                $this->logService->searchLog($searchRequest)
            )
        );
    }

    /**
     * Handle an incoming fetch log request.
     *
     * @param int $id
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/log/{id}', methods: ['GET'])]
    public function getLog(int $id): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD],
            (new LogAuthorization())
                ->setId($id)
                ->setRequiredRole([
                    TeamConfig::TEAM_MANAGER,
                    TeamConfig::TEAM_STANDARD,
                ])
        );

        return $this->json(
            new Response(
                true,
                "Get log",
                $this->logService->getLogById($id)
            )
        );
    }

    /**
     * Handle an incoming search app log request.
     *
     * @param int $appId
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/app/{appId}/log', methods: ['GET'])]
    public function searchAppLog(int $appId, Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD],
            (new AppAuthorization())
                ->setId($appId)
                ->setRequiredRole([
                    TeamConfig::TEAM_MANAGER,
                    TeamConfig::TEAM_STANDARD,
                ])
        );

        $searchRequest = $this->serialize(
            $request->query->all(),
            LogSearchRequest::class
        );

        return $this->json(
            new Response(
                true,
                "All app logs",
                $this->logService->searchAppLog($appId, $searchRequest)
            )
        );
    }

    /**
     * Handle an incoming fetch log of app request.
     *
     * @param int $appId
     * @param int $logId
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/app/{appId}/log/{logId}', methods: ['GET'])]
    public function getAppLog(int $appId, int $logId): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD],
            (new AppAuthorization())
                ->setId($appId)
                ->setRequiredRole([
                    TeamConfig::TEAM_MANAGER,
                    TeamConfig::TEAM_STANDARD,
                ])
        );

        return $this->json(
            new Response(
                true,
                "Get app log",
                $this->logService->getLogByAppIdAndLogId($appId, $logId)
            )
        );
    }

    /**
     * Handle an incoming save new log request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/log/save', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $logRequest = $this->serialize(
            $request->getContent(),
            LogRequest::class
        );
        $this->logService->create($logRequest);

        return $this->json(
            new Response(
                true,
                "Log has been saved"
            )
        );
    }
}