<?php

namespace App\Controller;

use App\Model\Request\MFARequest;
use App\Security\Authorization\AuthorizationCheckerInterface;
use App\Service\Auth\MFAServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MFAController extends BaseController
{
    private MFAServiceInterface $MFAService;

    public function __construct(
        MFAServiceInterface $MFAService,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->MFAService = $MFAService;

        parent::__construct($authorizationChecker);
    }

    /**
     * Handle an incoming validate two-factor authentication request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/api/auth/mfa/check', methods: ['POST'])]
    public function mfaCheck(Request $request): JsonResponse
    {
        $mfaRequest = $this->serialize(
            $request->getContent(),
            MFARequest::class
        );

        return $this->MFAService->check($mfaRequest, $request->getClientIp());
    }

    /**
     * Handle an incoming resend new OTP request.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/api/auth/mfa/resend', methods: ['POST'])]
    public function mfaResend(Request $request): JsonResponse
    {
        $mfaRequest = $this->serialize(
            $request->getContent(),
            MFARequest::class
        );

        return $this->MFAService->resend($mfaRequest, $request->getClientIp());
    }
}