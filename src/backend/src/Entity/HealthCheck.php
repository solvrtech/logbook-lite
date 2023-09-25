<?php

namespace App\Entity;

use App\Model\Response\HealthCheckResponse;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: '`health_check`')]
class HealthCheck implements ResponseEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: HealthStatus::class)]
    #[ORM\JoinColumn(
        name: 'health_status_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: 'CASCADE'
    )]
    private ?HealthStatus $healthStatus = null;

    #[ORM\Column(length: 50)]
    private ?string $checkKey = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $meta = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getHealthStatus(): ?HealthStatus
    {
        return $this->healthStatus;
    }

    public function setHealthStatus(?HealthStatus $healthStatus): self
    {
        $this->healthStatus = $healthStatus;

        return $this;
    }

    public function getCheckKey(): ?string
    {
        return $this->checkKey;
    }

    public function setCheckKey(?string $checkKey): self
    {
        $this->checkKey = $checkKey;

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

    public function getMeta(): ?string
    {
        return $this->meta;
    }

    public function setMeta(?string $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    public function toResponse(): HealthCheckResponse
    {
        return (new HealthCheckResponse())
            ->setCheckKey($this->checkKey)
            ->setStatus($this->status)
            ->setMeta($this->meta);
    }
}
