<?php

namespace App\Controller;

use App\Common\Config\TeamConfig;
use App\Common\Config\UserConfig;
use App\Model\Request\AppRequest;
use App\Model\Request\SearchRequest;
use App\Model\Response\Response;
use App\Security\Authorization\AppAuthorization;
use App\Security\Authorization\AuthorizationCheckerInterface;
use App\Service\App\AppServiceInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AppController extends BaseController
{
    private TokenStorageInterface $tokenStorage;
    private AppServiceInterface $appService;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AppServiceInterface $appService,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->appService = $appService;

        parent::__construct($authorizationChecker);
    }

    /**
     * Handle an incoming search app request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/app', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        //access control
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
                "Search app",
                $this->appService->searchApp($searchRequest)
            )
        );
    }

    /**
     * Handle an incoming fetch all app types request.
     *
     * @return JsonResponse
     */
    #[Route('/api/app/get-all-type', methods: ['GET'])]
    public function getAllAppTypes(): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD]
        );

        return $this->json(
            new Response(
                true,
                "All app types",
                $this->appService->getAllAppType()
            )
        );
    }

    /**
     * Handle an incoming fetch all apps request.
     *
     * @return JsonResponse
     */
    #[Route('/api/app/get-all', methods: ['GET'])]
    public function getAllApps(): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD]
        );

        return $this->json(
            new Response(
                true,
                "All apps",
                $this->appService->getNameAndIdAllApps()
            )
        );
    }

    /**
     * Handle an incoming fetch app by id request.
     *
     * @param int $id
     *
     * @return  JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/app/{id}', methods: ['GET'])]
    public function getApp(int $id): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD],
            (new AppAuthorization())
                ->setId($id)
                ->setRequiredRole([
                    TeamConfig::TEAM_MANAGER,
                ])
        );

        $app = $this->appService->getAppById($id)->toResponse();
        $app->setIsTeamManager(
            $this->tokenStorage
                ->getToken()
                ->getAttribute('isTeamManager')
        );

        return $this->json(
            new Response(
                true,
                "Get App",
                $app
            )
        );
    }

    /**
     * Handle an incoming fetch app common response by id request.
     *
     * @param int $id
     *
     * @return  JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/app/{id}/standard', methods: ['GET'])]
    public function getAppStandard(int $id): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD],
            (new AppAuthorization())
                ->setId($id)
                ->setRequiredRole([
                    TeamConfig::TEAM_MANAGER,
                    TeamConfig::TEAM_STANDARD,
                ])
        );

        $app = $this->appService->getAppById($id)
            ->toStandardTeamResponse();
        $app->setIsTeamManager(
            $this->tokenStorage
                ->getToken()
                ->getAttribute('isTeamManager')
        );

        return $this->json(
            new Response(
                true,
                "Get app with standard response",
                $app
            )
        );
    }

    /**
     * Handle an incoming save new app request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/app/create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission([UserConfig::ROLE_ADMIN]);

        $appRequest = $this->serialize(
            $request->getContent(),
            AppRequest::class
        );

        return $this->json(
            new Response(
                true,
                "Create new app",
                $this->appService->create($appRequest)->toResponse()
            )
        );
    }

    /**
     * Handle an incoming generate app API key request.
     *
     * @param int $id
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/app/{id}/generate-api-key', methods: ['POST'])]
    public function generateApiKey(int $id): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD],
            (new AppAuthorization())
                ->setId($id)
                ->setRequiredRole([
                    TeamConfig::TEAM_MANAGER,
                ])
        );

        return $this->json(
            new Response(
                true,
                "Generate new api key",
                $this->appService->generateApiKey($id)->toResponse()
            )
        );
    }

    /**
     * Handle an incoming update general information of app request.
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/app/{id}/edit-general', methods: ['PUT'])]
    public function updateGeneral(int $id, Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD],
            (new AppAuthorization())
                ->setId($id)
                ->setRequiredRole([
                    TeamConfig::TEAM_MANAGER,
                ])
        );

        $appRequest = $this->serialize(
            $request->getContent(),
            AppRequest::class
        );

        $app = $this->appService
            ->updateAppGeneral($appRequest, $id)
            ->toResponse();
        $app->setIsTeamManager(
            $this->tokenStorage
                ->getToken()
                ->getAttribute('isTeamManager')
        );

        return $this->json(
            new Response(
                true,
                "Update app general",
                $app
            )
        );
    }

    /**
     * Handle an incoming update teams of app request.
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/app/{id}/edit-teams', methods: ['PUT'])]
    public function updateTeams(int $id, Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission([UserConfig::ROLE_ADMIN]);

        $appRequest = $this->serialize(
            $request->getContent(),
            AppRequest::class
        );

        return $this->json(
            new Response(
                true,
                "Update app teams",
                $this->appService
                    ->updateAppTeams($appRequest, $id)
                    ->toResponse()
            )
        );
    }

    /**
     * Handle an incoming delete app request.
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/api/app/{id}/delete', methods: ['POST'])]
    public function delete(int $id, Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission([UserConfig::ROLE_ADMIN]);

        $appRequest = $this->serialize(
            $request->getContent(),
            AppRequest::class
        );

        return $this->json(
            new Response(
                true,
                "Delete app",
                $this->appService->delete($id, $appRequest)
            )
        );
    }
}