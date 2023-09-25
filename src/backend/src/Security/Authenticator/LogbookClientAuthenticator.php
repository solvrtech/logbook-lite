<?php

namespace App\Security\Authenticator;

use App\Security\LogbookClientToken;
use App\Service\App\AppServiceInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class LogbookClientAuthenticator extends AbstractAuthenticator
{
    private AppServiceInterface $appService;

    public function __construct(AppServiceInterface $appService)
    {
        $this->appService = $appService;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function supports(Request $request): ?bool
    {
        $token = $request->headers->has('x-lb-token');

        return $token ?: throw new Exception("API key not found");
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function authenticate(Request $request): Passport
    {
        $key = $request->headers->get('x-lb-token');

        try {
            $app = $this->appService->getAppByKey($key);
        } catch (Exception $e) {
            throw new Exception("App not registered");
        }

        $passport = new SelfValidatingPassport(
            new UserBadge(
                $app->getCreatedBy()->getEmail()
            )
        );
        $passport->setAttribute('clientApp', [
            'app' => $app,
            'version' => $request->headers->get('x-lb-version'),
            'instanceId' => $request->headers->get('x-lb-instance-id', 'default'),
        ]);

        return $passport;
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw new Exception("App not registered");
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        // read the attribute value
        return new LogbookClientToken(
            $passport->getUser(),
            $firewallName,
            $passport->getAttribute('clientApp'),
            $passport->getUser()->getRoles()
        );
    }
}