<?php

namespace App\Model\Response;

use App\Entity\AppLogo;
use App\Entity\BackupSetting;
use App\Entity\HealthStatusSetting;
use App\Entity\TeamApp;
use Doctrine\Common\Collections\Collection;

class AppResponse
{
    public ?int $id = null;
    public ?string $name = null;
    public ?string $description = null;
    public ?string $type = null;
    public ?string $appLogo = null;
    public ?string $apiKey = null;
    public ?bool $isTeamManager = null;
    public array $teamApp;
    public ?HealthStatusSettingResponse $appHealthSetting;
    public ?BackupSettingResponse $backupSetting;

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAppLogo(): ?string
    {
        return $this->appLogo;
    }

    public function setAppLogo(?AppLogo $appLogo): self
    {
        $this->appLogo = $appLogo?->getPublicPath();

        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function isTeamManager(): ?bool
    {
        return $this->isTeamManager;
    }

    public function setIsTeamManager(bool $hasAccess): self
    {
        $this->isTeamManager = $hasAccess;

        return $this;
    }

    public function getTeamApp(): array
    {
        return $this->teamApp;
    }

    public function setTeamApp(Collection|null $teamApp): self
    {
        if (null !== $teamApp) {
            if (!empty($teamApp->toArray())) {
                $this->teamApp = array_map(function (TeamApp $team) {
                    return [
                        'teamId' => $team->getTeam()->getId(),
                        'teamName' => $team->getTeam()->getName(),
                    ];
                }, $teamApp->toArray());
            }
        }

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

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

    public function getAppHealthSetting(): ?HealthStatusSettingResponse
    {
        return $this->appHealthSetting;
    }

    public function setAppHealthSetting(?HealthStatusSetting $appHealthSetting): self
    {
        $this->appHealthSetting = $appHealthSetting?->toResponse();

        return $this;
    }

    public function getBackupSetting(): ?BackupSettingResponse
    {
        return $this->backupSetting;
    }

    public function setBackupSetting(?BackupSetting $backupSetting): self
    {
        $this->backupSetting = $backupSetting?->toResponse();

        return $this;
    }
}