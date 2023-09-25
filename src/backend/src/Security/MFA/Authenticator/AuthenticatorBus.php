<?php

namespace App\Security\MFA\Authenticator;

use App\Common\Config\MFAConfig;
use App\Security\MFA\MFAInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class AuthenticatorBus implements AuthenticatorBusInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            MFAConfig::EMAIL_AUTHENTICATION => EmailAuthenticatorInterface::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthenticator(string $identifier): MFAInterface
    {
        if (!$this->container->has($identifier)) {
            throw new ServiceNotFoundException('2fa');
        }

        try {
            return $this->container->get($identifier);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $exception) {
            throw new ParameterNotFoundException("Identifier not found");
        }
    }
}