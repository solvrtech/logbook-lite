<?php

namespace App\Model\Response;

use App\Common\Config\MFAConfig;
use App\Entity\UserMFASetting;

class UserResponse
{
    public ?int $id = null;
    public string $email;
    public string $name;
    public string $mfa;
    public string $role;
    public array $assigned;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMfa(): string
    {
        return $this->mfa;
    }

    public function setMfa(?UserMFASetting $userMFASetting): self
    {
        $method = MFAConfig::EMAIL_AUTHENTICATION;

        if ($userMFASetting) {
            if (MFAConfig::GOOGLE_AUTHENTICATION === $userMFASetting->getMethod()) {
                $method = MFAConfig::GOOGLE_AUTHENTICATION;
            }
        }

        $this->mfa = $method;

        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getAssigned(): array
    {
        return $this->assigned;
    }

    public function setAssigned(array $assigned): self
    {
        $this->assigned = $assigned;

        return $this;
    }
}
