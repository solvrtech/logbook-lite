<?php

namespace App\Controller;

use App\Common\Config\UserConfig;
use App\Security\Authorization\AuthorizationCheckerInterface;
use App\Service\Role\RoleServiceInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class RoleController extends BaseController
{
    private RoleServiceInterface $roleService;

    public function __construct(
        RoleServiceInterface $roleService,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->roleService = $roleService;

        parent::__construct($authorizationChecker);
    }

    /**
     * Handle an incoming fetch all role request.
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    #[Route('/api/role/all-roles', methods: ['GET'])]
    public function getRoles(): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission([UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD]);

        return $this->roleService->getRoles();
    }
}
