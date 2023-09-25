<?php

namespace App\Security\MFA;

use App\Model\Response\MFAResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;

interface MFAInterface
{
    /**
     * Two-factor authentication should be performed for the user under current conditions.
     *
     * @param UserInterface $user
     * @param MFAResponse $response
     *
     * @return JsonResponse
     */
    public function isEnabled(UserInterface $user, MFAResponse $response): JsonResponse;

    /**
     * Get current authentication method.
     *
     * @return string
     */
    public function getMethod(): string;

    /**
     * Validate the given code.
     *
     * @param UserInterface $user
     * @param string $code
     *
     * @return bool
     */
    public function checkCode(UserInterface $user, string $code): bool;
}