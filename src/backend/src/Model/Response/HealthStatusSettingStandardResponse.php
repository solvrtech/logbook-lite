<?php

namespace App\Model\Response;

class HealthStatusSettingStandardResponse
{
    public bool $isEnabled = false;

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }
}