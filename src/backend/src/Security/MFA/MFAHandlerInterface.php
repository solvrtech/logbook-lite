<?php

namespace App\Security\MFA;

use Symfony\Component\Security\Core\User\UserInterface;

interface MFAHandlerInterface
{
    /**
     * Resend new otp for email authentication
     *
     * @param UserInterface $user
     *
     * @return bool
     */
    public function resend(UserInterface $user): bool;

    /**
     * Check the given code is valid
     *
     * @param UserInterface $user
     * @param string $token
     * @param string $ipClient
     *
     * @return bool
     */
    public function check(UserInterface $user, string $token, string $ipClient): bool;

    /**
     * Is still accepted to resend or check OTP
     *
     * @param UserInterface $user
     *
     * @return bool
     */
    public function isAccepted(UserInterface $user): bool;
}