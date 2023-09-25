<?php

namespace App\Security\Authentication;

use App\Security\MFA\MFAFactoryInterface;
use App\Service\Auth\AuthServiceInterface;
use App\Service\Setting\SettingServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private AuthServiceInterface $authService;
    private SettingServiceInterface $globalSettingService;
    private MFAFactoryInterface $MFAFactory;

    public function __construct(
        AuthServiceInterface    $authService,
        SettingServiceInterface $globalSettingService,
        MFAFactoryInterface     $MFAFactory
    )
    {
        $this->authService = $authService;
        $this->globalSettingService = $globalSettingService;
        $this->MFAFactory = $MFAFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $securitySetting = $this->globalSettingService->getSecuritySettingCached();

        if (
            null !== $securitySetting &&
            $securitySetting->getMfaAuthentication()
        )
            return $this->MFAFactory->perform(
                $token->getUser(),
                $securitySetting
            );

        return $this->authService->login($token->getUser());
    }
}