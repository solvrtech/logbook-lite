<?php

namespace App\Security\MFA;

use App\Common\Config\MFAConfig;
use App\Model\Response\MFAResponse;
use App\Model\Response\SecuritySettingResponse;
use App\Security\MFA\Authenticator\AuthenticatorBusInterface;
use App\Security\MFA\Event\MFAPerformEvent;
use App\Security\MFA\Event\MFASuccessEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MFAFactory implements MFAFactoryInterface
{
    public function __construct(
        private AuthenticatorBusInterface $authenticatorBus,
        private EventDispatcherInterface  $dispatcher
    )
    {
    }

    /**
     * {@inheritDoc}
     */
    public function perform(UserInterface $user, SecuritySettingResponse $securitySetting): JsonResponse
    {
        $authenticator = self::getAuthenticator($user);

        $event = new MFAPerformEvent($user, $authenticator->getMethod());
        $this->dispatcher->dispatch($event);

        $MFAResponse = (new  MFAResponse())
            ->setMfaStatus(true)
            ->setMfaMethod($authenticator->getMethod())
            ->setUserEmail($user->getUserIdentifier());

        return $authenticator->isEnabled($user, $MFAResponse);
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthenticator(UserInterface $user): MFAInterface
    {
        $userMFASetting = $user->getUserMFASetting();
        $authenticator = MFAConfig::EMAIL_AUTHENTICATION;

        if ($userMFASetting)
            $authenticator = $userMFASetting->getMethod();

        return $this->authenticatorBus->getAuthenticator($authenticator);
    }

    /**
     * {@inheritDoc}
     */
    public function reset(UserInterface $user): void
    {
        $authenticator = self::getAuthenticator($user);

        $event = new MFASuccessEvent($user, $authenticator->getMethod());
        $this->dispatcher->dispatch($event);
    }
}