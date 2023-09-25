<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordRequest
{
    #[Assert\NotBlank(groups: ['generate_token'])]
    #[Assert\Email(groups: ['generate_token'])]
    private ?string $email = null;

    #[Assert\NotBlank(groups: ['save'])]
    #[Assert\Length(min: 6, max: 50, groups: ['save'])]
    private ?string $password = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

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