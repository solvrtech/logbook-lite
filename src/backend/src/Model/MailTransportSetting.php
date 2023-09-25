<?php

namespace App\Model;

class MailTransportSetting
{
    private ?string $setting = null;
    private ?int $appId = null;

    public function getSetting(): ?string
    {
        return $this->setting;
    }

    public function setSetting(?string $setting): self
    {
        $this->setting = $setting;

        return $this;
    }

    public function getAppId(): ?int
    {
        return $this->appId;
    }

    public function setAppId(?int $appId): self
    {
        $this->appId = $appId;

        return $this;
    }
}