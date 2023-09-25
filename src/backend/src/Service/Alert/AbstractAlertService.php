<?php

namespace App\Service\Alert;

use App\Common\Config\AlertConfig;

abstract class AbstractAlertService implements AlertServiceInterface
{
    public ?array $alertConfig = null;
    public ?array $specificConfig = null;

    public function __construct(
        array $alertConfig,
        array $specificConfig
    ) {
        $this->alertConfig = $alertConfig;
        $this->specificConfig = $specificConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function useRestriction(): bool
    {
        return $this->alertConfig['restrictNotify'];
    }

    public function getAlertId(): int
    {
        return $this->alertConfig['id'];
    }

    public function getNotifyTo(): string
    {
        return $this->alertConfig['notifyTo'];
    }

    public function getNotifyLimit(): int
    {
        return $this->alertConfig['notifyLimit'];
    }

    public function getCacheKey(): string
    {
        return AlertConfig::APP_ALERT_LIMITER.$this->getSource();
    }

    public function getSource(): string
    {
        return $this->alertConfig['source'];
    }
}