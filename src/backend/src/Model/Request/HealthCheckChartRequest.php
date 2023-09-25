<?php

namespace App\Model\Request;

use App\Common\Config\HealthStatusConfig;
use Symfony\Component\Validator\Constraints as Assert;

class HealthCheckChartRequest extends ChartRequest
{
    #[Assert\NotBlank]
    #[Assert\Choice(
        [
            HealthStatusConfig::USED_DISK,
            HealthStatusConfig::MEMORY,
            HealthStatusConfig::DATABASE,
            HealthStatusConfig::CPU_LOAD,
            HealthStatusConfig::CACHE
        ]
    )]
    private ?string $checkKey = null;

    public function getCheckKey(): ?string
    {
        return $this->checkKey;
    }

    public function setCheckKey(?string $checkKey): self
    {
        $this->checkKey = $checkKey;

        return $this;
    }
}