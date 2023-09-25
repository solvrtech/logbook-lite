<?php

namespace App\Security\MFA\Authenticator;

use App\Security\MFA\MFAInterface;

interface EmailAuthenticatorInterface extends MFAInterface
{
    /**
     * Generate new one time password and send.
     *
     * @param string $userIdentifier
     */
    public function generateAndSend(string $userIdentifier): void;

    /**
     * Resend new one time password.
     *
     * @param string $email
     *
     * @return bool
     */
    public function resend(string $email): bool;

    /**
     * Delete the one time password
     *
     * @param string $userIdentifier
     */
    public function delete(string $userIdentifier): void;
}