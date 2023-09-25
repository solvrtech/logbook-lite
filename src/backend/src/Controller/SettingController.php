<?php

namespace App\Controller;

use App\Common\Config\TeamConfig;
use App\Common\Config\UserConfig;
use App\Model\Request\GeneralSettingRequest;
use App\Model\Request\HealthStatusSettingRequest;
use App\Model\Request\SecuritySettingRequest;
use App\Model\Response\Response;
use App\Model\Response\SecuritySettingResponse;
use App\Security\Authorization\AppAuthorization;
use App\Security\Authorization\AuthorizationCheckerInterface;
use App\Service\Setting\HealthStatusSettingServiceInterface;
use App\Service\Setting\SettingServiceInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SettingController extends BaseController
{
    private SettingServiceInterface $settingService;
    private HealthStatusSettingServiceInterface $appHealthSettingService;

    public function __construct(
        SettingServiceInterface $settingService,
        HealthStatusSettingServiceInterface $appHealthSettingService,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->settingService = $settingService;
        $this->appHealthSettingService = $appHealthSettingService;

        parent::__construct($authorizationChecker);
    }

    /**
     * Handle an incoming fetch global general setting request.
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/setting/general', methods: ['GET'])]
    public function getGeneralSetting(): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission([UserConfig::ROLE_ADMIN]);

        $setting = $this->settingService->getGeneralSetting();

        return $this->json(
            new Response(
                true,
                "Get general setting",
                $setting?->toResponse()
            )
        );
    }

    /**
     * Handle an incoming fetch global security setting request.
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/setting/security', methods: ['GET'])]
    public function getSecuritySetting(): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission([UserConfig::ROLE_ADMIN]);

        $setting = $this->settingService->getSecuritySetting()?->toResponse() ??
            new SecuritySettingResponse();

        return $this->json(
            new Response(
                true,
                "Get security setting",
                $setting
            )
        );
    }

    /**
     * Handle an incoming fetch all global setting request.
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/setting/all', methods: ['GET'])]
    public function getAllSetting(): JsonResponse
    {
        return $this->json(
            new Response(
                true,
                "Get all setting",
                $this->settingService->getAllSettingCached()
            )
        );
    }

    /**
     * Handle an incoming fetch all language request.
     *
     * @return JsonResponse
     */
    #[Route('/api/setting/language', methods: ['GET'])]
    public function getAllLanguage(): JsonResponse
    {
        return $this->json(
            new Response(
                true,
                "Geta all language",
                $this->settingService->getAllLanguage()
            )
        );
    }

    /**
     * Handle an incoming save health setting of app request.
     *
     * @param int $appId
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/app/{appId}/app-health-setting', methods: ['POST'])]
    public function saveAppHealthSetting(int $appId, Request $request): JsonResponse
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

        $appHealthRequest = $this->serialize(
            $request->getContent(),
            HealthStatusSettingRequest::class
        );
        $this->appHealthSettingService->save(
            $appHealthRequest,
            $appId
        );

        return $this->json(
            new Response(
                true,
                "Update app health setting"
            )
        );
    }

    /**
     * Handle an incoming save global general setting request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/setting/general', methods: ['POST'])]
    public function saveGeneralSetting(Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission([UserConfig::ROLE_ADMIN]);

        $generalRequest = $this->serialize(
            $request->getContent(),
            GeneralSettingRequest::class
        );
        $this->settingService->saveGeneralSetting($generalRequest);

        return $this->json(
            new Response(
                true,
                "Update general setting"
            )
        );
    }

    /**
     * Handle an incoming save global security setting  request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/setting/security', methods: ['POST'])]
    public function saveSecuritySetting(Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission([UserConfig::ROLE_ADMIN]);

        $securityRequest = $this->serialize(
            $request->getContent(),
            SecuritySettingRequest::class
        );
        $this->settingService->saveSecuritySetting($securityRequest);

        return $this->json(
            new Response(
                true,
                "Update security setting"
            )
        );
    }
}