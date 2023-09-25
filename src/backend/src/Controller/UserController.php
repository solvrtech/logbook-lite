<?php

namespace App\Controller;

use App\Common\Config\TeamConfig;
use App\Common\Config\UserConfig;
use App\Model\Request\SearchRequest;
use App\Model\Request\UserRequest;
use App\Model\Response\Response;
use App\Security\Authorization\AuthorizationCheckerInterface;
use App\Security\Authorization\LogAuthorization;
use App\Service\User\UserServiceInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends BaseController
{
    private UserServiceInterface $userService;

    public function __construct(
        UserServiceInterface $userService,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->userService = $userService;

        parent::__construct($authorizationChecker);
    }

    /**
     * Handle an incoming search user request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/user', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN]
        );

        $searchRequest = $this->serialize(
            $request->query->all(),
            SearchRequest::class
        );

        return $this->json(
            new Response(
                true,
                "Search user",
                $this->userService->searchUser($searchRequest)
            )
        );
    }

    /**
     * Handle an incoming get all standard users request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/user/all-standard', methods: ['GET'])]
    public function getAllStandardUser(Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD]
        );

        return $this->json(
            new Response(
                true,
                "All standard user",
                $this->userService->getAllStandardUsers()
            )
        );
    }

    /**
     * Handle an incoming get all users are assigned to the app by log id request.
     *
     * @param int $logId
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/user/log/{logId}', methods: ['GET'])]
    public function getAllUserByLog(int $logId, Request $request): JsonResponse
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

        return $this->json(
            new Response(
                true,
                "Get all user",
                $this->userService->searchUserByAppId()
            )
        );
    }

    /**
     * Handle an incoming fetch user request.
     *
     * @param int $id
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/user/{id}', methods: ['GET'])]
    public function getUserById(int $id): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission([UserConfig::ROLE_ADMIN]);

        return $this->json(
            new Response(
                true,
                "User",
                $this->userService->getUserById($id)->toResponse()
            )
        );
    }

    /**
     * Handle an incoming save new user request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/user/create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission([UserConfig::ROLE_ADMIN]);

        $userRequest = $this->serialize(
            $request->getContent(),
            UserRequest::class
        );
        $this->userService->create($userRequest);

        return $this->json(
            new Response(
                true,
                "Create new user"
            )
        );
    }

    /**
     * Handle an incoming update user request.
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/user/{id}/edit', methods: ['PUT'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission([UserConfig::ROLE_ADMIN]);

        $userRequest = $this->serialize(
            $request->getContent(),
            UserRequest::class
        );

        return $this->userService->update($userRequest, $id);
    }

    /**
     * Handle an incoming check allowed to delete a user request.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    #[Route('/api/user/{id}/allow-to-delete', methods: ['DELETE'])]
    public function isAllowedToDelete(int $id): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission([UserConfig::ROLE_ADMIN]);

        return $this->json(
            new Response(
                true,
                "Is allowed to delete the user",
                $this->userService->isAllowedToDelete(userId: $id)
            )
        );
    }

    /**
     * Handle an incoming delete user request.
     *
     * @param int $id
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/user/{id}/delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission([UserConfig::ROLE_ADMIN]);

        return $this->json(
            new Response(
                true,
                "Delete user",
                $this->userService->softDelete($id)
            )
        );
    }
}
