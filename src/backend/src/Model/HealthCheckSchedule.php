<?php

namespace App\Model;

use DateInterval;
use DateTime;

class HealthCheckSchedule
{
    private ?int $appId = null;
    private int $period = 15;
    private ?DateTime $lastCheck = null;
    private ?array $healthSetting = null;

    public function shouldRun(): bool
    {
        if (null !== $this->lastCheck) {
            $interval = new DateInterval("PT{$this->period}S");

            return (new DateTime())->sub($interval) >= $this->lastCheck;
        }

        return true;
    }

    public function getAppId(): ?int
    {
        return $this->appId;
    }

    public function setAppId(int $appId): self
    {
        $this->appId = $appId;

        return $this;
    }

    public function getPeriod(): int
    {
        return $this->period;
    }

    public function setPeriod(int $period): self
    {
        $this->period = $period;

        return $this;
    }

    public function getLastCheck(): DateTime
    {
        return $this->lastCheck;
    }

    public function setLastCheck(DateTime $lastCheck): self
    {
        $this->lastCheck = $lastCheck;

        return $this;
    }

    public function getHealthSetting(): array
    {
        return $this->healthSetting;
    }

    public function setHealthSetting(array $healthSetting): self
    {
        $this->healthSetting = $healthSetting;

        return $this;
    }
}
