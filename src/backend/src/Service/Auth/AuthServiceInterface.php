<?php

namespace App\Service\Auth;

use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;

interface AuthServiceInterface
{
    /**
     * Sign in attempt with the given $user.
     *
     * @param UserInterface $user
     *
     * @return JsonResponse
     */
    public function login(UserInterface $user): JsonResponse;

    /**
     * Generate a new access token when it is needed to refresh the token.
     *
     * @param array $cookies
     *
     * @return JsonResponse
     */
    public function refresh(array $cookies): JsonResponse;

    /**
     * Generate a new access token when the logged user updates his data.
     *
     * @param User $user
     *
     * @return JsonResponse
     */
    public function generateNewToken(User $user): JsonResponse;

    /**
     * Destroy all access token.
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse;
}
