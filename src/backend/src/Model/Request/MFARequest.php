<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;

class MFARequest
{
    #[Assert\NotBlank(groups: ['check', 'google'])]
    private ?string $otpToken = null;

    #[Assert\NotBlank(groups: ['check', 'resend', 'recovery'])]
    #[Assert\Email(groups: ['check', 'resend', 'recovery'])]
    private ?string $email = null;

    #[Assert\NotBlank(groups: ['recovery'])]
    private ?string $recoveryKey = null;

    public function getOtpToken(): ?string
    {
        return $this->otpToken;
    }

    public function setOtpToken(?string $otpToken): self
    {
        $this->otpToken = $otpToken;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getRecoveryKey(): ?string
    {
        return $this->recoveryKey;
    }

    public function setRecoveryKey(?string $recoveryKey): self
    {
        $this->recoveryKey = $recoveryKey;

        return $this;
    }
}