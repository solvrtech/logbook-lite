<?php

namespace App\Entity;

use App\Model\Response\AppResponse;
use App\Model\Response\AppStandardResponse;
use App\Model\Response\AppStandardTeamResponse;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: '`app`')]
class App implements ResponseEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 300)]
    private ?string $description = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(length: 128)]
    private ?string $apiKey = null;

    #[ORM\OneToOne(mappedBy: 'app', targetEntity: AppLogo::class, cascade: ['persist'])]
    private ?AppLogo $appLogo = null;

    #[ORM\OneToOne(mappedBy: 'app', targetEntity: HealthStatusSetting::class, cascade: ['persist'])]
    private ?HealthStatusSetting $HealthStatusSetting = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(
        name: 'created_by',
        referencedColumnName: 'id',
        nullable: false
    )]
    private ?User $createdBy = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'modified_by', referencedColumnName: 'id')]
    private ?User $modifiedBy = null;

    #[ORM\OneToMany(mappedBy: 'app', targetEntity: TeamApp::class, cascade: ['persist'])]
    private Collection $teamApp;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTime $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $modifiedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getHealthStatusSetting(): ?HealthStatusSetting
    {
        return $this->HealthStatusSetting;
    }

    public function setHealthStatusSetting(?HealthStatusSetting $HealthStatusSetting): self
    {
        // unset the owning side of the relation if necessary
        if (null === $HealthStatusSetting && null !== $this->HealthStatusSetting) {
            $this->HealthStatusSetting->setApp(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $HealthStatusSetting && $this !== $HealthStatusSetting->getApp()) {
            $HealthStatusSetting->setApp($this);
        }

        $this->HealthStatusSetting = $HealthStatusSetting;

        return $this;
    }

    public function getAppLogo(): ?AppLogo
    {
        return $this->appLogo;
    }

    public function setAppLogo(?AppLogo $appLogo): self
    {
        if (null === $appLogo && null !== $this->appLogo) {
            $this->appLogo->setApp(null);
        }

        if (null !== $appLogo && $this !== $appLogo->getApp()) {
            $appLogo->setApp($this);
        }

        $this->appLogo = $appLogo;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getModifiedBy(): ?User
    {
        return $this->modifiedBy;
    }

    public function setModifiedBy(?User $modifiedBy): self
    {
        $this->modifiedBy = $modifiedBy;

        return $this;
    }

    public function getTeamApp(): Collection
    {
        return $this->teamApp;
    }

    public function addTeamApp(TeamApp $teamApp): self
    {
        if (!$this->teamApp->contains($teamApp)) {
            $this->teamApp[] = $teamApp;
            $teamApp->setApp($this);
        }

        return $this;
    }

    public function removeTeamApp(TeamApp $teamApp): self
    {
        if ($this->teamApp->removeElement($teamApp)) {
            if ($teamApp->getApp() === $this) {
                $teamApp->setApp(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getModifiedAt(): ?DateTime
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(?DateTime $modifiedAt): self
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    public function toResponse(): AppResponse
    {
        return (new AppResponse())
            ->setId($this->id)
            ->setName($this->name)
            ->setDescription($this->description)
            ->setType($this->type)
            ->setAppLogo($this->appLogo)
            ->setApiKey($this->apiKey)
            ->setTeamApp($this->teamApp ?? null)
            ->setAppHealthSetting($this->HealthStatusSetting);
    }

    public function toStandardResponse(): AppStandardResponse
    {
        return (new AppStandardResponse())
            ->setId($this->id)
            ->setName($this->name)
            ->setType($this->type)
            ->setDescription($this->description)
            ->setAppLogo($this->appLogo?->getPublicPath());
    }

    public function toStandardTeamResponse(): AppStandardTeamResponse
    {
        return (new AppStandardTeamResponse())
            ->setId($this->id)
            ->setName($this->name)
            ->setType($this->type)
            ->setAppLogo($this->appLogo?->getPublicPath())
            ->setAppHealthSetting($this->HealthStatusSetting);
    }
}