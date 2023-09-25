<?php

namespace App\Security\Authorization;

class TeamAccessConfig implements TeamAccessConfigInterface
{
    private ?int $id = null;
    private array $requiredRole;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getRequiredRole(): array
    {
        return $this->requiredRole;
    }

    public function setRequiredRole(array $requiredRole): self
    {
        $this->requiredRole = $requiredRole;

        return $this;
    }
}