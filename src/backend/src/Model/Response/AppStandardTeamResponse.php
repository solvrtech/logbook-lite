<?php

namespace App\Model\Response;

use App\Entity\HealthStatusSetting;

class AppStandardTeamResponse extends AppStandardResponse
{
    public bool $isTeamManager = false;
    public ?HealthStatusSettingStandardResponse $appHealthSetting;

    public function isIsTeamManager(): bool
    {
        return $this->isTeamManager;
    }

    public function setIsTeamManager(bool $isTeamManager): self
    {
        $this->isTeamManager = $isTeamManager;

        return $this;
    }

    public function getAppHealthSetting(): ?HealthStatusSettingStandardResponse
    {
        return $this->appHealthSetting;
    }

    public function setAppHealthSetting(?HealthStatusSetting $healthStatusSetting): self
    {
        $this->appHealthSetting = $healthStatusSetting?->toStandardResponse();

        return $this;
    }
}