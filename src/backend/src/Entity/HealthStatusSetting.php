<?php

namespace App\Entity;

use App\Model\Response\HealthStatusSettingResponse;
use App\Model\Response\HealthStatusSettingStandardResponse;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: '`health_status_setting`')]
class HealthStatusSetting implements ResponseEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: App::class)]
    #[ORM\JoinColumn(
        name: 'app_id',
        referencedColumnName: 'id',
        onDelete: 'CASCADE'
    )]
    private ?App $app = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $isEnabled = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(nullable: true)]
    private ?int $period = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTime $updatedAt = null;

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

    public function getIsEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(?bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getPeriod(): ?int
    {
        return $this->period;
    }

    public function setPeriod(?int $period): self
    {
        $this->period = $period;

        return $this;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function toResponse(): HealthStatusSettingResponse
    {
        return (new HealthStatusSettingResponse())
            ->setIsEnabled($this->isEnabled)
            ->setUrl($this->url)
            ->setPeriod($this->period);
    }

    public function toStandardResponse():HealthStatusSettingStandardResponse {
        return (new HealthStatusSettingStandardResponse())
            ->setIsEnabled($this->isEnabled);
    }
}