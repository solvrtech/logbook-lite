<?php

namespace App\Model\Response;

use App\Common\DateTimeHelper;
use DateTime;

class LogResponse
{
    public ?int $id = null;
    public ?string $instanceId = null;
    public ?string $message = null;
    public ?string $file = null;
    public array $stackTrace;
    public ?string $level = null;
    public ?string $dateTime = null;
    public array $additional;
    public ?string $browser = null;
    public ?string $os = null;
    public ?string $device = null;
    public ?string $status = null;
    public ?string $priority = null;
    public ?bool $isTeamManager = null;
    public ?UserResponse $assignee = null;
    public ?array $tag = null;
    public mixed $appVersion = null;
    public ?AppStandardResponse $app = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getInstanceId(): ?string
    {
        return $this->instanceId;
    }

    public function setInstanceId(?string $instanceId): self
    {
        $this->instanceId = $instanceId;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getStackTrace(): array
    {
        return $this->stackTrace;
    }

    public function setStackTrace(array $stackTrace): self
    {
        $this->stackTrace = $stackTrace;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(?string $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getDateTime(): ?string
    {
        return $this->dateTime;
    }

    public function setDateTime(?DateTime $dateTime): self
    {
        $this->dateTime = (new DateTimeHelper())
            ->dateTimeToStr($dateTime);

        return $this;
    }

    public function getAdditional(): array
    {
        return $this->additional;
    }

    public function setAdditional(array $additional): self
    {
        $this->additional = $additional;

        return $this;
    }

    public function getBrowser(): ?string
    {
        return $this->browser;
    }

    public function setBrowser(?string $browser): self
    {
        $this->browser = $browser;

        return $this;
    }

    public function getOs(): ?string
    {
        return $this->os;
    }

    public function setOs(?string $os): self
    {
        $this->os = $os;

        return $this;
    }

    public function getDevice(): ?string
    {
        return $this->device;
    }

    public function setDevice(?string $device): self
    {
        $this->device = $device;

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

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(?string $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function isTeamManager(): ?bool
    {
        return $this->isTeamManager;
    }

    public function setIsTeamManager(?bool $isTeamManager): self
    {
        $this->isTeamManager = $isTeamManager;

        return $this;
    }

    public function getAssignee(): ?UserResponse
    {
        return $this->assignee;
    }

    public function setAssignee(?UserResponse $assignee): self
    {
        $this->assignee = $assignee;

        return $this;
    }

    public function getTag(): ?array
    {
        return $this->tag;
    }

    public function setTag(?array $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function getAppVersion(): mixed
    {
        return $this->appVersion;
    }

    public function setAppVersion(mixed $appVersion): self
    {
        if (null !== $appVersion) {
            $this->appVersion = json_decode($appVersion);
        }

        return $this;
    }

    public function getApp(): ?AppStandardResponse
    {
        return $this->app;
    }

    public function setApp(?AppStandardResponse $app): self
    {
        $this->app = $app;

        return $this;
    }
}