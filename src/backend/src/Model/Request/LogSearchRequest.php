<?php

namespace App\Model\Request;

use App\Common\Config\LogConfig;
use App\Common\DateTimeHelper;
use Symfony\Component\Validator\Constraints as Assert;

class LogSearchRequest extends SearchRequest
{
    private ?string $level = null;

    #[Assert\Type('int')]
    private ?int $app = null;

    #[Assert\Choice([
        LogConfig::NEW,
        LogConfig::ON_REVIEW,
        LogConfig::IGNORED,
        LogConfig::RESOLVED,
    ])]
    private ?string $status = null;

    private ?array $tag = null;

    #[Assert\Type('int')]
    private ?int $assignee = null;

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

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(?string $level): self
    {
        if ($level) {
            $this->level = strtolower($level);
        }

        return $this;
    }

    public function getApp(): ?int
    {
        return $this->app;
    }

    public function setApp(?int $app): void
    {
        $this->app = $app;
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

    public function getTag(): ?string
    {
        return !empty($this->tag) ? json_encode($this->tag) : null;
    }

    public function setTag(?string $tag): self
    {
        $this->tag = !empty($tag) ? explode(',', $tag) : [];

        return $this;
    }

    public function getAssignee(): ?int
    {
        return $this->assignee;
    }

    public function setAssignee(?int $assignee): void
    {
        $this->assignee = $assignee;
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