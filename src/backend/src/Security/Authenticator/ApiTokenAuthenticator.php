<?php

namespace App\Security\Authenticator;

use App\Security\Exception\AuthException;
use App\Security\TokenProviderInterface;
use App\Service\User\UserServiceInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiTokenAuthenticator extends AbstractAuthenticator
{
    private UserServiceInterface $userService;
    private TokenProviderInterface $tokenProvider;

    public function __construct(
        UserServiceInterface $userService,
        TokenProviderInterface $tokenProvider
    ) {
        $this->userService = $userService;
        $this->tokenProvider = $tokenProvider;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     *
     * {@inheritDoc}
     *
     * @param Request $request
     *
     * @return bool|null
     *
     * @throws AuthException
     */
    public function supports(Request $request): ?bool
    {
        $cookies = $request->cookies->all();

        if (empty($cookies)) {
            throw new AuthException("No token provided");
        }

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function authenticate(Request $request): Passport
    {
        $accessToken = $request->cookies->get('accessToken');

        if (null === $accessToken) {
            throw new AuthException("No token provided");
        }

        $payload = $this->tokenProvider->decode($accessToken);
        $passport = new SelfValidatingPassport(
            new UserBadge(
                $payload->email,
                function () use ($payload) {
                    return self::loadUser($payload->email);
                }
            )
        );
        $passport->setAttribute('email', $payload->email);

        return $passport;
    }

    /**
     * Retrieve UserInterface from given $identity
     *
     * @param string $identity
     *
     * @return UserInterface
     *
     * @throws Exception
     */
    private function loadUser(string $identity): UserInterface
    {
        $user = $this->userService->getUserByEmail($identity);

        if (null === $user) {
            throw new AuthException("User not found");
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     *
     * @return Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * {@inheritDoc}
     *
     * @return Response|JsonResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $response = [
            'success' => false,
            'message' => strtr(
                $exception->getMessageKey(),
                $exception->getMessageData()
            ),
        ];

        return new JsonResponse($response, Response::HTTP_OK);
    }
}
