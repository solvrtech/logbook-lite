<?php

namespace App\Entity;

use App\Common\Config\HealthStatusConfig;
use App\Model\Response\HealthStatusResponse;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: '`health_status`')]
class HealthStatus implements ResponseEntityInterface
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

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $instanceId = null;

    #[ORM\OneToMany(
        mappedBy: 'healthStatus',
        targetEntity: HealthCheck::class,
        cascade: ['persist']
    )]
    private Collection $healthCheck;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTime $createdAt = null;

    public function __construct()
    {
        $this->healthCheck = new ArrayCollection();
    }

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

    public function getInstanceId(): ?string
    {
        return $this->instanceId;
    }

    public function setInstanceId(?string $instanceId): self
    {
        $this->instanceId = $instanceId;

        return $this;
    }

    public function getHealthCheck(): Collection
    {
        return $this->healthCheck;
    }

    public function addHealthCheck(HealthCheck $healthCheck): self
    {
        if (!$this->healthCheck->contains($healthCheck)) {
            $this->healthCheck[] = $healthCheck;
            $healthCheck->setHealthStatus($this);
        }

        return $this;
    }

    public function removeHealthCheck(HealthCheck $healthCheck): self
    {
        if ($this->healthCheck->removeElement($healthCheck)) {
            if ($healthCheck->getHealthStatus() === $this) {
                $healthCheck->setHealthStatus(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function toResponse(): HealthStatusResponse
    {
        return (new HealthStatusResponse())
            ->setId($this->id)
            ->setStatus($this->status)
            ->setCreatedAt($this->createdAt)
            ->setHealthCheck($this->healthCheck)
            ->setTotalFailed(
                $this->healthCheck->filter(function (HealthCheck $healthCheck) {
                    return $healthCheck->getStatus() === HealthStatusConfig::FAILED;
                })->count()
            );
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
}
