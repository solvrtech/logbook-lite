<?php

namespace App\Security\MFA\Model;

use DateTime;

class OTP
{
    private ?string $otp = null;
    private ?DateTime $otpExpired = null;

    public function getOtp(): ?string
    {
        return $this->otp;
    }

    public function setOtp(?string $otp): self
    {
        $this->otp = $otp;

        return $this;
    }

    public function getOtpExpired(): ?DateTime
    {
        return $this->otpExpired;
    }

    public function setOtpExpired(?DateTime $otpExpired): self
    {
        $this->otpExpired = $otpExpired;

        return $this;
    }
}