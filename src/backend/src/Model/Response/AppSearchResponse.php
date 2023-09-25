<?php

namespace App\Model\Response;

class AppSearchResponse extends AppStandardResponse
{
    public ?string $description = null;
    public bool $isTeamManager = false;
    public bool $backupActive = false;

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

    public function backupActive(): bool
    {
        return $this->backupActive;
    }

    public function setBackupActive(?bool $backupActive): self
    {
        $this->backupActive = $backupActive ?? false;

        return $this;
    }
}