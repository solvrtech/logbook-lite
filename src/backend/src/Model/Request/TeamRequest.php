<?php

namespace App\Model\Request;

use App\Common\Config\TeamConfig;
use Symfony\Component\Validator\Constraints as Assert;

class TeamRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $name = null;

    #[Assert\NotBlank]
    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Collection([
            'userId' => [
                new Assert\NotBlank(),
                new Assert\Type('int'),
            ],
            'role' => [
                new Assert\NotBlank(),
                new Assert\Choice([TeamConfig::TEAM_MANAGER, TeamConfig::TEAM_STANDARD]),
            ],
        ]),
    ])]
    private ?array $user = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUser(): ?array
    {
        return $this->user;
    }

    public function setUser(?array $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function addUser(int $userId, string $role): self
    {
        $this->user[] = [
            'userId' => $userId,
            'role' => $role,
        ];

        return $this;
    }
}