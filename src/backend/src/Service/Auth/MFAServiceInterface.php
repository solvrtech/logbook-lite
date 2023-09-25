<?php

namespace App\Service\Auth;

use App\Model\Request\MFARequest;
use Symfony\Component\HttpFoundation\JsonResponse;

interface MFAServiceInterface
{
    /**
     * Resend new OTP token.
     *
     * @param MFARequest $request
     * @param string $ipClient
     *
     * @return JsonResponse
     */
    public function resend(MFARequest $request, string $ipClient): JsonResponse;

    /**
     * Check the OTP
     *
     * @param MFARequest $request
     * @param string $ipClient
     *
     * @return JsonResponse
     */
    public function check(MFARequest $request, string $ipClient): JsonResponse;

}