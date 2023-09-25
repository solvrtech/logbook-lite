<?php

namespace App\Controller;

use App\Common\Config\UserConfig;
use App\Model\Request\UserRequest;
use App\Security\Authorization\AuthorizationCheckerInterface;
use App\Service\User\UserProfileServiceInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserProfileController extends BaseController
{
    private UserProfileServiceInterface $userProfileService;

    public function __construct(
        UserProfileServiceInterface $userProfileService,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->userProfileService = $userProfileService;

        parent::__construct($authorizationChecker);
    }

    /**
     * Handle an incoming update profile request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/user-profile/edit', methods: ['PUT'])]
    public function edit(Request $request): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD]
        );

        $userRequest = $this->serialize(
            $request->getContent(),
            UserRequest::class
        );

        return $this->userProfileService->update($userRequest);
    }
}