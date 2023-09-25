<?php

namespace App\Controller;

use App\Common\Config\TeamConfig;
use App\Common\Config\UserConfig;
use App\Model\Request\HealthStatusSearchRequest;
use App\Model\Response\Response;
use App\Security\Authorization\AppAuthorization;
use App\Security\Authorization\AuthorizationCheckerInterface;
use App\Security\Authorization\HealthStatusAuthorization;
use App\Service\Health\HealthStatusServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HealthStatusController extends BaseController
{
    private HealthStatusServiceInterface $healthStatusService;

    public function __construct(
        HealthStatusServiceInterface $healthStatusService,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->healthStatusService = $healthStatusService;

        parent::__construct($authorizationChecker);
    }

    /**
     * Handle an incoming search app health status request.
     *
     * @param int $appId
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/api/app/{appId}/health-status', methods: ['GET'])]
    public function searchAppHealthStatus(int $appId, Request $request): JsonResponse
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
            HealthStatusSearchRequest::class
        );

        return $this->json(
            new Response(
                true,
                "Search app health status",
                $this->healthStatusService->search($searchRequest, $appId)
            )
        );
    }

    /**
     * Handle an incoming search health status request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/api/health-status', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD]
        );

        $searchRequest = $this->serialize(
            $request->query->all(),
            HealthStatusSearchRequest::class
        );

        return $this->json(
            new Response(
                true,
                "Search health status",
                $this->healthStatusService->search($searchRequest)
            )
        );
    }

    /**
     * Handle an incoming fetch app health status by id request.
     *
     * @param int $appId
     * @param int $healthStatusId
     *
     * @return JsonResponse
     */
    #[Route('/api/app/{appId}/health-status/{healthStatusId}', methods: ['GET'])]
    public function getAppHealthStatus(int $appId, int $healthStatusId): JsonResponse
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
                "Get app health status",
                $this->healthStatusService->getAppHealthStatus(
                    $appId,
                    $healthStatusId
                )->toResponse()
            )
        );
    }

    /**
     * Handle an incoming fetch health status request.
     *
     * @param int $healthStatusId
     *
     * @return JsonResponse
     */
    #[Route('/api/health-status/{healthStatusId}', methods: ['GET'])]
    public function getHealthStatus(int $healthStatusId): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD],
            (new HealthStatusAuthorization())
                ->setId($healthStatusId)
                ->setRequiredRole([
                    TeamConfig::TEAM_MANAGER,
                    TeamConfig::TEAM_STANDARD,
                ])
        );

        return $this->json(
            new Response(
                true,
                "Get health status",
                $this->healthStatusService
                    ->getHealthStatus($healthStatusId)
                    ->toResponse()
            )
        );
    }
}