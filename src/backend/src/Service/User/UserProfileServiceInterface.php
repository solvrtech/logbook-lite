<?php

namespace App\Service\User;

use App\Model\Request\UserRequest;
use Symfony\Component\HttpFoundation\JsonResponse;

interface UserProfileServiceInterface
{
    /**
     * Update user profile matching.
     *
     * @param UserRequest $userRequest
     *
     * @return JsonResponse
     */
    public function update(UserRequest $userRequest): JsonResponse;
}