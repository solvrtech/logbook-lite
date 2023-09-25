<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;

class SecuritySettingRequest
{
    #[Assert\NotBlank]
    #[Assert\Type('int')]
    #[Assert\Range(min: 1, max: 20)]
    private ?int $loginMaxFailed = null;

    #[Assert\NotBlank]
    #[Assert\Type('int')]
    #[Assert\Range(min: 1, max: 999)]
    private ?int $loginInterval = null;

    #[Assert\Type('bool')]
    private ?bool $mfaAuthentication = null;

    #[Assert\Type('int')]
    #[Assert\Range(min: 1, max: 59)]
    private ?int $mfaDelayResend = null;

    #[Assert\Type('int')]
    #[Assert\Range(min: 1, max: 20)]
    private ?int $mfaMaxResend = null;

    #[Assert\Type('int')]
    #[Assert\Range(min: 1, max: 20)]
    private ?int $mfaMaxFailed = null;

    #[Assert\IsTrue(message: "mfaDelayResend, mfaMaxResend, and mfaMaxFailed must filled")]
    public function isMfaAuthentication(): bool
    {
        if ($this->mfaAuthentication) {
            if (
                null === $this->mfaDelayResend ||
                null === $this->mfaMaxResend ||
                null === $this->mfaMaxFailed
            ) {
                return false;
            }
        }

        return true;
    }

    public function getLoginMaxFailed(): ?int
    {
        return $this->loginMaxFailed;
    }

    public function setLoginMaxFailed(?int $loginMaxFailed): self
    {
        $this->loginMaxFailed = $loginMaxFailed;

        return $this;
    }

    public function getLoginInterval(): ?int
    {
        return $this->loginInterval;
    }

    public function setLoginInterval(?int $loginInterval): self
    {
        $this->loginInterval = $loginInterval;

        return $this;
    }

    public function getMfaAuthentication(): ?bool
    {
        return $this->mfaAuthentication;
    }

    public function setMfaAuthentication(?bool $mfaAuthentication): self
    {
        $this->mfaAuthentication = $mfaAuthentication;

        return $this;
    }

    public function getMfaDelayResend(): ?int
    {
        return $this->mfaDelayResend;
    }

    public function setMfaDelayResend(?int $mfaDelayResend): self
    {
        $this->mfaDelayResend = $mfaDelayResend;

        return $this;
    }

    public function getMfaMaxResend(): ?int
    {
        return $this->mfaMaxResend;
    }

    public function setMfaMaxResend(?int $mfaMaxResend): self
    {
        $this->mfaMaxResend = $mfaMaxResend;

        return $this;
    }

    public function getMfaMaxFailed(): ?int
    {
        return $this->mfaMaxFailed;
    }

    public function setMfaMaxFailed(?int $mfaMaxFailed): self
    {
        $this->mfaMaxFailed = $mfaMaxFailed;

        return $this;
    }
}