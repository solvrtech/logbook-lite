<?php

namespace App\Model\Request;

use App\Common\Config\HealthStatusConfig;
use App\Common\DateTimeHelper;
use Symfony\Component\Validator\Constraints as Assert;

class HealthStatusSearchRequest
{
    #[Assert\Type('int')]
    #[Assert\Range(min: 0)]
    private ?int $page = null;

    #[Assert\Type('int')]
    #[Assert\Range(min: 0)]
    private ?int $size = null;

    #[Assert\Type('int')]
    private ?int $app = null;

    #[Assert\Choice([
        HealthStatusConfig::OK,
        HealthStatusConfig::FAILED,
    ])]
    private ?string $status = null;

    #[Assert\When(
        expression: 'this.getEndDateTime() !== null',
        constraints: [
            new Assert\NotBlank(),
        ]
    )]
    private ?string $startDateTime = null;

    #[Assert\When(
        expression: 'this.getStartDateTime() !== null',
        constraints: [
            new Assert\NotBlank(),
        ]
    )]
    private ?string $endDateTime = null;

    #[Assert\IsTrue(message: "startDateTime not valid")]
    public function isStartDateTime(): bool
    {
        if (null === $this->startDateTime) {
            return true;
        }

        return false !== (new DateTimeHelper())
                ->dateTimeFromFormat($this->startDateTime);
    }

    #[Assert\IsTrue(message: "endDateTime not valid")]
    public function isEndDateTime(): bool
    {
        if (null === $this->endDateTime) {
            return true;
        }

        return false !== (new DateTimeHelper())
                ->dateTimeFromFormat($this->endDateTime);
    }

    public function offset(): int
    {
        return $this->getSize() * ($this->getPage() - 1);
    }

    public function getSize(): ?int
    {
        return $this->size ?? 25;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size > 0 ? $size : 25;

        return $this;
    }

    public function getPage(): ?int
    {
        return $this->page ?? 1;
    }

    public function setPage(?int $page): self
    {
        $this->page = $page > 0 ? $page : 1;

        return $this;
    }

    public function getApp(): ?int
    {
        return $this->app;
    }

    public function setApp(?int $app): self
    {
        $this->app = $app;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        if ($status) {
            $this->status = strtolower($status);
        }

        return $this;
    }

    public function getStartDateTime(): ?string
    {
        return $this->startDateTime;
    }

    public function setStartDateTime(?string $startDateTime): self
    {
        $this->startDateTime = $startDateTime;

        return $this;
    }

    public function getEndDateTime(): ?string
    {
        return $this->endDateTime;
    }

    public function setEndDateTime(?string $endDateTime): self
    {
        $this->endDateTime = $endDateTime;

        return $this;
    }
}