<?php

namespace App\Service\Auth;

use App\Entity\User;
use App\Security\Exception\AuthException;
use App\Security\TokenProviderInterface;
use App\Service\BaseService;
use Exception;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthService
    extends BaseService
    implements AuthServiceInterface
{
    private TokenProviderInterface $tokenProvider;

    public function __construct(
        TokenProviderInterface $tokenProvider
    ) {
        $this->tokenProvider = $tokenProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function login(UserInterface $user): JsonResponse
    {
        $accessToken = $this->tokenProvider
            ->generateAccessToken($user->getUserIdentifier());
        $refreshToken = $this->tokenProvider
            ->generateRefreshToken($user->getUserIdentifier());

        $response = self::setCookies($accessToken, $refreshToken);
        $response->setContent(
            json_encode([
                'success' => true,
                'message' => "Login",
                'accessTokenExpiration' => $accessToken['exp'],
                'refreshTokenExpiration' => $refreshToken['exp'],
            ])
        );

        return $response;
    }

    /**
     * Add access and refresh token to cookies.
     *
     * @param array $accessToken
     * @param array $refreshToken
     *
     * @return JsonResponse
     */
    private function setCookies(array $accessToken, array $refreshToken): JsonResponse
    {
        $response = new JsonResponse();
        $cookieSecure = filter_var(
            $this->getParam('cookie_secure'),
            FILTER_VALIDATE_BOOL
        );

        // set access token
        $response->headers->setcookie(
            new Cookie(
                "accessToken",
                $accessToken['token'],
                $accessToken['exp'],
                '/',
                null,
                $cookieSecure
            )
        );

        // set refresh token
        $response->headers->setcookie(
            new Cookie(
                "refreshToken",
                $refreshToken['token'],
                $refreshToken['exp'],
                '/',
                null,
                $cookieSecure
            )
        );

        return $response;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function refresh(array $cookies): JsonResponse
    {
        if (0 === count($cookies) || !isset($cookies['refreshToken'])) {
            throw new AuthException("Refresh tokens was failed");
        }

        $user = $this->tokenProvider->getUser($cookies['refreshToken']);
        $accessToken = $this->tokenProvider
            ->generateAccessToken($user->getEmail());
        $refreshToken = $this->tokenProvider
            ->generateRefreshToken($user->getEmail());

        $response = self::setCookies($accessToken, $refreshToken);
        $response->setContent(
            json_encode([
                'success' => true,
                'message' => "Refresh token",
                'accessTokenExpiration' => $accessToken['exp'],
                'refreshTokenExpiration' => $refreshToken['exp'],
            ])
        );

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function generateNewToken(User $user): JsonResponse
    {
        $accessToken = $this->tokenProvider
            ->generateAccessToken($user->getUserIdentifier());
        $refreshToken = $this->tokenProvider
            ->generateRefreshToken($user->getUserIdentifier());

        return self::setCookies($accessToken, $refreshToken);
    }

    /**
     * {@inheritDoc}
     */
    public function logout(): JsonResponse
    {
        $response = new JsonResponse();

        $response->headers->setcookie(new Cookie("accessToken"));
        $response->headers->setcookie(new Cookie("refreshToken"));
        $response->setContent(
            json_encode([
                'success' => true,
                'message' => "Logout",
                'data' => null,
            ])
        );

        return $response;
    }
}
