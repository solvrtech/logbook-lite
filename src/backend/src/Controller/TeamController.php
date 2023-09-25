<?php

namespace App\Controller;

use App\Common\Config\TeamConfig;
use App\Common\Config\UserConfig;
use App\Model\Request\SearchRequest;
use App\Model\Request\TeamRequest;
use App\Model\Response\Response;
use App\Security\Authorization\AuthorizationCheckerInterface;
use App\Security\Authorization\TeamAuthorization;
use App\Service\Team\TeamServiceInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TeamController extends BaseController
{
    private TeamServiceInterface $teamService;

    public function __construct(
        TeamServiceInterface $teamService,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->teamService = $teamService;

        parent::__construct($authorizationChecker);
    }

    /**
     * Handle an incoming search team request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/team', methods: ['GET'])]
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
                "Search team",
                $this->teamService->searchTeam($searchRequest)
            )
        );
    }

    /**
     * Handle an incoming get all teams request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/team/all-team', methods: ['GET'])]
    public function getAllTeams(Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission([UserConfig::ROLE_ADMIN]);

        return $this->json(
            new Response(
                true,
                "All teams",
                $this->teamService->getAllTeam()
            )
        );
    }

    /**
     * Handle an incoming fetch team request.
     *
     * @param int $id
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/team/{id}', methods: ['GET'])]
    public function getTeam(int $id): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD],
            (new TeamAuthorization())
                ->setId($id)
                ->setRequiredRole([
                    TeamConfig::TEAM_MANAGER,
                    TeamConfig::TEAM_STANDARD,
                ])
        );

        return $this->json(
            new Response(
                true,
                "Team",
                $this->teamService->getTeamById($id)->toResponse()
            )
        );
    }

    /**
     * Handle an incoming save new team request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/team/create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission([UserConfig::ROLE_ADMIN]);

        $teamRequest = $this->serialize(
            $request->getContent(),
            TeamRequest::class
        );
        $this->teamService->create($teamRequest);

        return $this->json(
            new Response(
                true,
                "Team has been saved"
            )
        );
    }

    /**
     * Handle an incoming update team request.
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/team/{id}/edit', methods: ['PUT'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD],
            (new TeamAuthorization())
                ->setId($id)
                ->setRequiredRole([
                    TeamConfig::TEAM_MANAGER,
                ])
        );

        $teamRequest = $this->serialize(
            $request->getContent(),
            TeamRequest::class
        );
        $this->teamService->update($teamRequest, $id);

        return $this->json(
            new Response(
                true,
                "Team has been updated"
            )
        );
    }

    /**
     * Handle an incoming delete team request.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws Exception
     */
    #[Route('/api/team/{id}/delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission([UserConfig::ROLE_ADMIN]);

        $this->teamService->delete($id);

        return $this->json(
            new Response(
                true,
                "Delete team"
            )
        );
    }
}