<?php

namespace App\Model\Request;

use App\Common\Config\AlertConfig;
use App\Common\Config\HealthStatusConfig;
use App\Common\Config\LogConfig;
use App\Common\Config\TeamConfig;
use Symfony\Component\Validator\Constraints as Assert;

class AlertSettingRequest
{
    #[Assert\NotBlank]
    #[Assert\Choice([AlertConfig::LOG_SOURCE, AlertConfig::HEALTH_SOURCE])]
    public ?string $source = null;

    #[Assert\Type('bool')]
    public ?bool $restrictNotify = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $name = null;

    #[Assert\Type('bool')]
    private ?bool $active = null;

    #[Assert\When(
        expression: 'this.source === "log"',
        constraints: [
            new Assert\NotBlank,
            new Assert\Collection(
                fields: [
                    'manyFailures' => [
                        new Assert\NotBlank,
                        new Assert\Type('int'),
                        new Assert\Length(min: 1),
                    ],
                    'duration' => [
                        new Assert\NotBlank,
                        new Assert\Type('int'),
                        new Assert\Length(min: 1),
                    ],
                    'level' => [
                        new Assert\NotBlank,
                        new Assert\Type('array'),
                        new Assert\All([
                            new Assert\Collection([
                                'level' => [
                                    new Assert\Choice([
                                        LogConfig::DEBUG,
                                        LogConfig::INFO,
                                        LogConfig::NOTICE,
                                        LogConfig::WARNING,
                                        LogConfig::ERROR,
                                        LogConfig::CRITICAL,
                                        LogConfig::ALERT,
                                        LogConfig::EMERGENCY,
                                    ]),
                                ],
                            ]),
                        ]),
                    ],
                    'message' => new Assert\Length(max: 255),
                    'stackTrace' => new Assert\Length(max: 255),
                    'browser' => new Assert\Length(max: 50),
                    'os' => new Assert\Length(max: 50),
                    'device' => new Assert\Length(max: 50),
                    'additional' => new Assert\Length(max: 255),
                ]
            ),
        ],
    )]
    #[Assert\When(
        expression: 'this.source === "health"',
        constraints: [
            new Assert\NotBlank,
            new Assert\Collection(
                fields: [
                    'manyFailures' => [
                        new Assert\NotBlank,
                        new Assert\Type('int'),
                        new Assert\Length(min: 1),
                    ],
                    'specific' => [
                        new Assert\Type('array'),
                        new Assert\All([
                            new Assert\Collection(
                                fields: [
                                    'checkKey' => [
                                        new Assert\NotBlank,
                                        new Assert\Choice([
                                            HealthStatusConfig::CACHE,
                                            HealthStatusConfig::CPU_LOAD,
                                            HealthStatusConfig::DATABASE,
                                            HealthStatusConfig::MEMORY,
                                            HealthStatusConfig::USED_DISK,
                                        ]),
                                    ],
                                    'item' => [
                                        new Assert\NotBlank,
                                        new Assert\Choice([
                                            HealthStatusConfig::STATUS,
                                            HealthStatusConfig::LAST_MINUTES,
                                            HealthStatusConfig::LAST_5_MINUTES,
                                            HealthStatusConfig::LAST_15_MINUTES,
                                            HealthStatusConfig::DATABASE_SIZE,
                                            HealthStatusConfig::MEMORY_USAGE,
                                            HealthStatusConfig::USED_DISK_SPACE,
                                        ]),
                                    ],
                                    'value' => [
                                        new Assert\NotBlank,
                                    ],
                                ]
                            ),
                        ]),
                    ],
                ]
            ),
        ]
    )]
    private ?array $config = null;

    #[Assert\NotBlank]
    #[Assert\Choice(["ALL", TeamConfig::TEAM_MANAGER, TeamConfig::TEAM_STANDARD])]
    private ?string $notifyTo = null;

    #[Assert\When(
        expression: 'this.restrictNotify === true',
        constraints: [
            new Assert\NotBlank,
            new Assert\Length(min: 1),
        ],
    )]
    #[Assert\Type('int')]
    #[Assert\Range(min: 0)]
    private ?int $notifyLimit = null;

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

    public function getConfig(): ?array
    {
        return $this->config;
    }

    public function setConfig(?array $config): self
    {
        $this->config = $config;

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
}