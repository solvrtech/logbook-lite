<?php

namespace App\Security\MFA;

use App\Model\Response\SecuritySettingResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;

interface MFAFactoryInterface
{
    /**
     * Perform two-factor authentication.
     *
     * @param UserInterface $user
     * @param SecuritySettingResponse $securitySetting
     *
     * @return JsonResponse
     */
    public function perform(UserInterface $user, SecuritySettingResponse $securitySetting): JsonResponse;

    /**
     * Get current user authenticator
     *
     * @param UserInterface $user
     *
     * @return MFAInterface
     */
    public function getAuthenticator(UserInterface $user): MFAInterface;

    /**
     * Reset all limiter
     *
     * @param UserInterface $user
     */
    public function reset(UserInterface $user): void;
}