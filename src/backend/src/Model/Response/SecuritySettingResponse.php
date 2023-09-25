<?php

namespace App\Model\Response;

use App\Common\Config\AuthConfig;

class SecuritySettingResponse
{
    public int $loginMaxFailed = AuthConfig::DEFAULT_LOGIN_FAIL;
    public int $loginInterval = AuthConfig::DEFAULT_LOGIN_INTERVAL;
    public bool $mfaAuthentication = false;
    public ?int $mfaDelayResend = null;
    public ?int $mfaMaxResend = null;
    public ?int $mfaMaxFailed = null;

    public function getLoginMaxFailed(): int
    {
        return $this->loginMaxFailed;
    }

    public function setLoginMaxFailed(?int $loginMaxFailed): self
    {
        $this->loginMaxFailed = $loginMaxFailed ?? AuthConfig::DEFAULT_LOGIN_FAIL;

        return $this;
    }

    public function getLoginInterval(): int
    {
        return $this->loginInterval;
    }

    public function setLoginInterval(?int $loginInterval): self
    {
        $this->loginInterval = $loginInterval ?? AuthConfig::DEFAULT_LOGIN_INTERVAL;

        return $this;
    }

    public function getMfaAuthentication(): bool
    {
        return $this->mfaAuthentication;
    }

    public function setMfaAuthentication(bool|null $mfaAuthentication): self
    {
        $this->mfaAuthentication = boolval($mfaAuthentication);

        return $this;
    }

    public function getMfaDelayResend(): int
    {
        return $this->mfaDelayResend;
    }

    public function setMfaDelayResend(int|null $mfaDelayResend): self
    {
        $this->mfaDelayResend = $mfaDelayResend;

        return $this;
    }

    public function getMfaMaxResend(): int
    {
        return $this->mfaMaxResend;
    }

    public function setMfaMaxResend(int|null $mfaMaxResend): self
    {
        $this->mfaMaxResend = $mfaMaxResend;

        return $this;
    }

    public function getMfaMaxFailed(): int
    {
        return $this->mfaMaxFailed;
    }

    public function setMfaMaxFailed(int|null $mfaMaxFailed): self
    {
        $this->mfaMaxFailed = $mfaMaxFailed;

        return $this;
    }
}