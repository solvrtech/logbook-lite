<?php

namespace App\Model\Request;

use App\Common\Config\UserConfig;
use App\Validator as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

class UserRequest
{
    #[Assert\NotBlank(groups: ['create', 'update', 'profile'])]
    #[Assert\Email(groups: ['create', 'update', 'profile'])]
    #[AppAssert\EmailUnique(groups: ['profile'])]
    private ?string $email = null;

    #[Assert\NotBlank(groups: ['create', 'update', 'profile', 'invite'])]
    #[Assert\Length(max: 100, groups: ['create', 'update', 'profile', 'invite'])]
    private ?string $name = null;

    #[Assert\NotBlank(groups: ['create', 'update'])]
    #[Assert\Length(max: 50, groups: ['create', 'update'])]
    private ?string $role = null;

    #[Assert\NotBlank(groups: ['create', 'invite'])]
    #[Assert\Length(min: 8, max: 50, groups: ['create', 'update', 'profile', 'invite'])]
    private ?string $password = null;

    #[Assert\IsTrue(message: "Role not registered", groups: ['create', 'update'])]
    public function isRoleRegistered(): bool
    {
        return in_array($this->role, [UserConfig::ROLE_ADMIN, UserConfig::ROLE_STANDARD]);
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }
}
