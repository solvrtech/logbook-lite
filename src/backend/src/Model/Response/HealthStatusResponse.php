<?php

namespace App\Model\Response;

use App\Common\DateTimeHelper;
use App\Entity\HealthCheck;
use DateTime;
use Doctrine\Common\Collections\Collection;

class HealthStatusResponse
{
    public ?int $id = null;
    public array $healthCheck;
    public ?string $status = null;
    public ?string $createdAt = null;
    public ?int $totalFailed = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getHealthCheck(): array
    {
        return $this->healthCheck;
    }

    public function setHealthCheck(Collection $healthCheck): self
    {
        $this->healthCheck = array_map(function (HealthCheck $healthCheck) {
            return $healthCheck->toResponse();
        }, $healthCheck->toArray());

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTime $createdAt): self
    {
        $this->createdAt = (new DateTimeHelper())
            ->dateTimeToStr($createdAt);

        return $this;
    }

    public function getTotalFailed(): ?int
    {
        return $this->totalFailed;
    }

    public function setTotalFailed(?int $totalFailed): self
    {
        $this->totalFailed = $totalFailed;

        return $this;
    }
}