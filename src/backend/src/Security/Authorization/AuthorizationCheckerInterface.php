<?php

namespace App\Security\Authorization;

interface AuthorizationCheckerInterface
{
    /**
     * Checks if the permission is granted against the current authentication token.
     *
     * @param array $requiredRoles
     * @param TeamAccessConfigInterface|null $teamAccessConfig
     */
    public function accessCheck(array $requiredRoles, ?TeamAccessConfigInterface $teamAccessConfig = null): void;
}