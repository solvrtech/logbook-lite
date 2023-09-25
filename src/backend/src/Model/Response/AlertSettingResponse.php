<?php

namespace App\Model\Response;

use App\Common\DateTimeHelper;
use DateTime;

class AlertSettingResponse
{
    private ?int $id = null;
    private ?string $name = null;
    private ?bool $active = null;
    private ?string $source = null;
    private array|object|null $config = null;
    private ?string $notifyTo = null;
    private bool $restrictNotify = false;
    private ?int $notifyLimit = null;
    private ?string $lastNotified = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getConfig(): array|object|null
    {
        return $this->config;
    }

    public function setConfig(string $config): self
    {
        $this->config = json_decode($config);

        return $this;
    }

    public function getRestrictNotify(): ?bool
    {
        return $this->restrictNotify;
    }

    public function setRestrictNotify(?bool $restrictNotify): self
    {
        $this->restrictNotify = $restrictNotify;

        return $this;
    }

    public function getNotifyTo(): ?string
    {
        return $this->notifyTo;
    }

    public function setNotifyTo(?string $notifyTo): self
    {
        $this->notifyTo = $notifyTo;

        return $this;
    }

    public function getNotifyLimit(): ?int
    {
        return $this->notifyLimit;
    }

    public function setNotifyLimit(?int $notifyLimit): self
    {
        $this->notifyLimit = $notifyLimit;

        return $this;
    }

    public function getLastNotified(): ?string
    {
        return $this->lastNotified;
    }

    public function setLastNotified(?DateTime $lastNotified): self
    {
        if ($lastNotified) {
            $this->lastNotified = (new DateTimeHelper())
                ->dateTimeToStr($lastNotified);
        }

        return $this;
    }
}
