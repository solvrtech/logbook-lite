<?php

namespace App\Service\Role;

use Symfony\Component\HttpFoundation\JsonResponse;

interface RoleServiceInterface
{
    /**
     * Get all registered roles from.
     *
     * @return JsonResponse
     */
    public function getRoles(): JsonResponse;
}
