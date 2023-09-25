<?php

namespace App\Model\Response;

class MFAResponse
{
    public bool $mfaStatus = false;
    public ?string $mfaMethod = null;
    public ?string $userEmail = null;

    public function isMfaStatus(): bool
    {
        return $this->mfaStatus;
    }

    public function setMfaStatus(bool $mfaStatus): self
    {
        $this->mfaStatus = $mfaStatus;

        return $this;
    }

    public function getMfaMethod(): ?string
    {
        return $this->mfaMethod;
    }

    public function setMfaMethod(?string $mfaMethod): self
    {
        $this->mfaMethod = $mfaMethod;

        return $this;
    }

    public function getUserEmail(): ?string
    {
        return $this->userEmail;
    }

    public function setUserEmail(?string $userEmail): self
    {
        $this->userEmail = $userEmail;

        return $this;
    }
}