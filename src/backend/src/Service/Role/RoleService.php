<?php

namespace App\Service\Role;

use App\Model\Response\Response;
use App\Service\BaseService;
use Symfony\Component\HttpFoundation\JsonResponse;

class RoleService extends BaseService implements RoleServiceInterface
{
    /**
     * {@inheritDoc}
     */
    public function getRoles(): JsonResponse
    {
        $roles = [];
        foreach ($this->getParam('roles') as $val) {
            $roles[] = [
                'role' => $val,
            ];
        }

        $role = [
            'info' => $this->getParam('role_info'),
            'roles' => $roles,
        ];

        return $this->json(
            new Response(
                true,
                "Roles",
                $role
            )
        );
    }
}
