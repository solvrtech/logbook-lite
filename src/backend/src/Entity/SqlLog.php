<?php

namespace App\Entity;

use App\Common\Config\LogConfig;
use App\Model\Response\LogResponse;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use InvalidArgumentException;

#[ORM\Entity]
#[ORM\Table(name: '`log`')]
class SqlLog implements ResponseEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $appId = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $instanceId = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column(nullable: true)]
    private ?string $file = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $stackTrace = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $code = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $level = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTime $dateTime = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $additional = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $browser = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $os = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $device = null;

    #[ORM\Column(length: 300, nullable: true)]
    private ?string $client = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $version = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $priority = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $assignee = null;

    #[ORM\Column(nullable: true)]
    private ?string $tag = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getAppId(): ?int
    {
        return $this->appId;
    }

    public function setAppId(?int $app): self
    {
        $this->appId = $app;

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

    public function setMessage(string $message): self
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

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(?int $code): self
    {
        $this->code = $code;

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

    public function getDateTime(): ?DateTime
    {
        return $this->dateTime;
    }

    /**
     * @throws Exception
     */
    public function setDateTime(DateTime|string $dateTime): self
    {
        if (is_string($dateTime)) {
            $dateTime = new DateTime($dateTime);
        }
        $this->dateTime = $dateTime;

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

    public function getClient(): ?string
    {
        return $this->client;
    }

    public function setClient(?string $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        if (
            $status &&
            !in_array(
                $status,
                array(LogConfig::NEW, LogConfig::ON_REVIEW, LogConfig::IGNORED, LogConfig::RESOLVED)
            )
        ) {
            throw new InvalidArgumentException("Invalid status");
        }

        $this->status = $status;

        return $this;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(?string $priority): self
    {
        if (
            $priority &&
            !in_array(
                $priority,
                array(
                    LogConfig::HIGHEST_PRIORITY,
                    LogConfig::CRITICAL_PRIORITY,
                    LogConfig::HIGH_PRIORITY,
                    LogConfig::MEDIUM_PRIORITY,
                    LogConfig::LOW_PRIORITY,
                )
            )
        ) {
            throw new InvalidArgumentException("Invalid status");
        }

        $this->priority = $priority;

        return $this;
    }

    public function getAssignee(): ?int
    {
        return $this->assignee;
    }

    public function setAssignee(?int $assignee): self
    {
        $this->assignee = $assignee;

        return $this;
    }

    public function toResponse(): LogResponse
    {
        return (new LogResponse())
            ->setId($this->id)
            ->setInstanceId($this->instanceId)
            ->setMessage($this->message)
            ->setFile($this->file)
            ->setStackTrace(self::getStackTrace())
            ->setLevel($this->level)
            ->setDateTime($this->dateTime)
            ->setAdditional(self::getAdditional())
            ->setBrowser($this->browser)
            ->setOs($this->os)
            ->setDevice($this->device)
            ->setAppVersion($this->version)
            ->setStatus($this->status)
            ->setPriority($this->priority)
            ->setTag(self::getTag());
    }

    public function getStackTrace(): ?array
    {
        return json_decode($this->stackTrace);
    }

    public function setStackTrace(?string $stackTrace): self
    {
        $this->stackTrace = $stackTrace;

        return $this;
    }

    public function getAdditional(): ?array
    {
        return json_decode($this->additional);
    }

    public function setAdditional(?string $additional): self
    {
        $this->additional = $additional;

        return $this;
    }

    public function getTag(): ?array
    {
        return json_decode($this->tag);
    }

    public function setTag(?string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }
}