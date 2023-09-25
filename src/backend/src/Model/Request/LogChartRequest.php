<?php

namespace App\Model\Request;

use App\Common\DateTimeHelper;
use Symfony\Component\Validator\Constraints as Assert;

class LogChartRequest
{
    #[Assert\NotBlank]
    private ?string $startDate = null;

    #[Assert\NotBlank]
    private ?string $endDate = null;

    #[Assert\NotBlank]
    #[Assert\Type('array')]
    private ?array $level = null;

    #[Assert\Type('int')]
    #[Assert\Range(min: 0)]
    private ?int $limit = null;

    #[Assert\IsTrue(message: "Date range is not valid")]
    public function isStartDate(): bool
    {
        $dateTimeHelper = new DateTimeHelper();
        $startDate = $dateTimeHelper->dateTimeFromFormat(
            $this->startDate,
            dateOnly: true
        );
        $endDate = $dateTimeHelper->dateTimeFromFormat(
            $this->endDate,
            dateOnly: true
        );

        if ($startDate === false || $endDate === false) {
            return false;
        }

        $interval = $endDate->diff($startDate);

        return $interval->days <= 14 && $interval->days >= 0;
    }

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function setStartDate(?string $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function setEndDate(?string $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getLevel(): ?array
    {
        return array_unique($this->level);
    }

    public function setLevel(?array $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getLimit(): ?int
    {
        return $this->limit ?? 10;
    }

    public function setLimit(?int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }
}