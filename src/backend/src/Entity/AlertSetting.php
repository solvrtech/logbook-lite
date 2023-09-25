<?php

namespace App\Entity;

use App\Model\Response\AlertSettingResponse;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;

#[ORM\Entity]
#[ORM\Table(name: '`alert_setting`')]
class AlertSetting implements ResponseEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: App::class)]
    #[ORM\JoinColumn(
        name: 'app_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: 'CASCADE'
    )]
    private ?App $app = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $active = false;

    #[ORM\Column(length: 50)]
    private ?string $source = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $config = null;

    #[ORM\Column(length: 50)]
    private ?string $notifyTo = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $restrictNotify = false;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $notifyLimit = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $lastNotified = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTime $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $modifiedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getApp(): ?App
    {
        return $this->app;
    }

    public function setApp(?App $app): self
    {
        $this->app = $app;

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

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
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

    public function getConfig(): ?string
    {
        return $this->config;
    }

    public function setConfig(?string $config): self
    {
        $this->config = $config;

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

    public function getRestrictNotify(): bool
    {
        return $this->restrictNotify;
    }

    public function setRestrictNotify(bool $restrictNotify): self
    {
        $this->restrictNotify = $restrictNotify;

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

    public function getLastNotified(): ?DateTime
    {
        return $this->lastNotified;
    }

    /**
     * @throws Exception
     */
    public function setLastNotified(DateTime|string|null $lastNotified): self
    {
        if (is_string($lastNotified)) {
            $lastNotified = new DateTime($lastNotified);
        }

        $this->lastNotified = $lastNotified;

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * @throws Exception
     */
    public function setCreatedAt(DateTime|string $createdAt): self
    {
        if (is_string($createdAt)) {
            $createdAt = new DateTime($createdAt);
        }

        $this->createdAt = $createdAt;

        return $this;
    }

    public function getModifiedAt(): ?DateTime
    {
        return $this->modifiedAt;
    }

    /**
     * @throws Exception
     */
    public function setModifiedAt(DateTime|string|null $modifiedAt): self
    {
        if (is_string($modifiedAt)) {
            $modifiedAt = new DateTime($modifiedAt);
        }

        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    public function toResponse(): AlertSettingResponse
    {
        return (new AlertSettingResponse())
            ->setId($this->id)
            ->setName($this->name)
            ->setActive($this->active)
            ->setSource($this->source)
            ->setConfig($this->config)
            ->setRestrictNotify($this->restrictNotify)
            ->setNotifyTo($this->notifyTo)
            ->setNotifyLimit($this->notifyLimit)
            ->setLastNotified($this->lastNotified);
    }
}
