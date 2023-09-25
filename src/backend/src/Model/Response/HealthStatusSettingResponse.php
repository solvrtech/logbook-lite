<?php

namespace App\Model\Response;

class HealthStatusSettingResponse
{
    public bool $isEnabled = false;
    public ?string $url = null;
    public ?int $period = null;

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(?bool $isEnabled): self
    {
        $this->isEnabled = boolval($isEnabled);

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getPeriod(): ?int
    {
        return $this->period;
    }

    public function setPeriod(?int $period): self
    {
        $this->period = $period;

        return $this;
    }
}