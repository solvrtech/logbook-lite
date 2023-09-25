<?php

namespace App\Controller;

use App\Common\Config\TeamConfig;
use App\Common\Config\UserConfig;
use App\Model\Request\AlertSettingRequest;
use App\Model\Request\SearchRequest;
use App\Model\Response\Response;
use App\Security\Authorization\AppAuthorization;
use App\Security\Authorization\AuthorizationCheckerInterface;
use App\Service\Setting\AlertSettingServiceInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AlertSettingController extends BaseController
{
    private AlertSettingServiceInterface $alertService;

    public function __construct(
        AlertSettingServiceInterface $alertService,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->alertService = $alertService;

        parent::__construct($authorizationChecker);
    }

    /**
     * Handle an incoming search app alert request.
     *
     * @param int $appId
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/app/{appId}/alert', methods: ['GET'])]
    public function searchAppAlert(int $appId, Request $request): JsonResponse
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
            SearchRequest::class
        );

        return $this->json(
            new Response(
                true,
                "All app alerts",
                $this->alertService->searchAppAlert($appId, $searchRequest)
            )
        );
    }

    /**
     * Handle an incoming fetch app alert by alert id request.
     *
     * @param int $appId
     * @param int $alertId
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/app/{appId}/alert/{alertId}', methods: ['GET'])]
    public function getAppAlert(int $appId, int $alertId): JsonResponse
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
                "Get app alert",
                $this->alertService->getAppAlert($appId, $alertId)->toResponse()
            )
        );
    }

    /**
     * Handle an incoming save new app alert request.
     *
     * @param int $appId
     * @Param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/app/{appId}/alert/create', methods: ['POST'])]
    public function create(int $appId, Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD],
            (new AppAuthorization())
                ->setId($appId)
                ->setRequiredRole([
                    TeamConfig::TEAM_MANAGER,
                ])
        );

        $alertRequest = $this->serialize(
            $request->getContent(),
            AlertSettingRequest::class
        );

        $this->alertService->create($alertRequest, $appId);

        return $this->json(
            new Response(
                true,
                "Create new app alert"
            )
        );
    }

    /**
     * Handle an incoming update app alert request.
     *
     * @param int $appId
     * @param int $alertId
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/app/{appId}/alert/{alertId}/edit', methods: ['PUT'])]
    public function edit(int $appId, int $alertId, Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD],
            (new AppAuthorization())
                ->setId($appId)
                ->setRequiredRole([
                    TeamConfig::TEAM_MANAGER,
                ])
        );

        $alertRequest = $this->serialize(
            $request->getContent(),
            AlertSettingRequest::class
        );

        $this->alertService->update($alertRequest, $appId, $alertId);

        return $this->json(
            new Response(
                true,
                "Update app alert"
            )
        );
    }

    /**
     * Handle an incoming delete app alert request.
     *
     * @Param int $appId
     * @param int $alertId
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/app/{appId}/alert/{alertId}/delete', methods: ['DELETE'])]
    public function delete(int $appId, int $alertId): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD],
            (new AppAuthorization())
                ->setId($appId)
                ->setRequiredRole([
                    TeamConfig::TEAM_MANAGER,
                ])
        );

        $this->alertService->delete($appId, $alertId);

        return $this->json(
            new Response(
                true,
                "Delete app alert"
            )
        );
    }
}