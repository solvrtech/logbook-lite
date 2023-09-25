<?php

namespace App\Controller;

use App\Common\Config\UserConfig;
use App\Model\Request\ResetPasswordRequest;
use App\Model\Response\Response;
use App\Security\Authorization\AuthorizationCheckerInterface;
use App\Service\Auth\AuthServiceInterface;
use App\Service\Auth\ResetPasswordServiceInterface;
use App\Service\User\UserServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthController extends BaseController
{
    private AuthServiceInterface $authService;
    private UserServiceInterface $userService;
    private ResetPasswordServiceInterface $resetPasswordService;

    public function __construct(
        AuthServiceInterface $authService,
        UserServiceInterface $userService,
        ResetPasswordServiceInterface $resetPasswordService,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->authService = $authService;
        $this->userService = $userService;
        $this->resetPasswordService = $resetPasswordService;

        parent::__construct($authorizationChecker);
    }

    /**
     * Handle an incoming sign in request.
     *
     * @param UserInterface $user
     *
     * @return void
     */
    #[Route('/api/auth/login', name: 'auth_login', methods: ['POST'])]
    public function login(UserInterface $user): void
    {
        //
    }

    /**
     * Handle an incoming refresh JWT token request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/api/auth/refresh', methods: ['POST'])]
    public function refresh(Request $request): JsonResponse
    {
        $cookies = $request->cookies->all();

        return $this->authService->refresh($cookies);
    }

    /**
     * Handle an incoming get current user signed request.
     *
     * @return JsonResponse
     */
    #[Route('/api/auth/me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD]
        );

        return $this->json(
            new Response(
                true,
                "Current user",
                $this->userService->currentUser()
            )
        );
    }

    /**
     * Handle an incoming sign out request.
     *
     * @return JsonResponse
     */
    #[Route('/api/auth/logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        //access control
        $this->denyAccessUnlessPermission(
            [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD]
        );

        return $this->authService->logout();
    }

    /**
     * Handle an incoming reset password request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/api/auth/reset-password', methods: ['POST'])]
    public function resetPassword(Request $request): JsonResponse
    {
        $resetPasswordRequest = $this->serialize(
            $request->getContent(),
            ResetPasswordRequest::class
        );

        return $this->json(
            new Response(
                true,
                "Reset password",
                $this->resetPasswordService->reset($resetPasswordRequest)
            )
        );
    }

    /**
     * Handle an incoming checking token reset password request.
     *
     * @param string $token
     *
     * @return JsonResponse
     */
    #[Route('/api/auth/set-password/{token}/valid', methods: ['GET'])]
    public function isResetPasswordValid(string $token): JsonResponse
    {
        return $this->json(
            new Response(
                true,
                "Token is valid",
                $this->resetPasswordService
                    ->isTokenValid($token)
                    ->toResponse()
            )
        );
    }

    /**
     * Handle an incoming save new password request.
     *
     * @param string $token
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/api/auth/set-password/{token}', methods: ['POST'])]
    public function saveNewPassword(string $token, Request $request): JsonResponse
    {
        $resetPasswordRequest = $this->serialize(
            $request->getContent(),
            ResetPasswordRequest::class
        );

        return $this->json(
            new Response(
                true,
                "Set password",
                $this->resetPasswordService
                    ->savePassword($resetPasswordRequest, $token)
            )
        );
    }
}
