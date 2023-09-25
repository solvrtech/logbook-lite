<?php

namespace App\Security\RateLimiter;

use App\Security\RateLimiter\Model\OTPResendModel;

interface ResendOTPRateLimiterInterface
{
    public function consume(string $userIdentifier): OTPResendModel;

    public function peek(string $userIdentifier): OTPResendModel;

    public function reset(string $userIdentifier): void;
}