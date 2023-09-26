<?php

namespace App\Model\Response;

class AppSearchResponse extends AppStandardResponse
{
    public ?string $description = null;
    public bool $isTeamManager = false;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function isIsTeamManager(): bool
    {
        return $this->isTeamManager;
    }

    public function setIsTeamManager(bool $isTeamManager): self
    {
        $this->isTeamManager = $isTeamManager;

        return $this;
    }
}