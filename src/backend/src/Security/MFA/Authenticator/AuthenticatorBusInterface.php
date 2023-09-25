<?php

namespace App\Security\MFA\Authenticator;

use App\Security\MFA\MFAInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

interface AuthenticatorBusInterface extends ServiceSubscriberInterface
{
    /**
     * Get authenticator matching with the given identifier.
     *
     * @param string $identifier
     *
     * @return MFAInterface
     */
    public function getAuthenticator(string $identifier): MFAInterface;
}