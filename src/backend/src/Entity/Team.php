<?php

namespace App\Entity;

use App\Model\Response\TeamResponse;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: '`team`')]
class Team implements ResponseEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(
        name: 'created_by',
        referencedColumnName: 'id',
        nullable: false
    )]
    private ?User $createdBy = null;

    #[ORM\OneToMany(mappedBy: 'team', targetEntity: UserTeam::class)]
    private Collection $userTeam;

    #[ORM\OneToMany(mappedBy: 'team', targetEntity: TeamApp::class)]
    private Collection $teamApp;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTime $createdAt = null;

    public function __construct()
    {
        $this->userTeam = new ArrayCollection();
        $this->teamApp = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

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

    public function getUserTeam(): Collection
    {
        return $this->userTeam;
    }

    public function addUserTeam(UserTeam $userTeam): self
    {
        if (!$this->userTeam->contains($userTeam)) {
            $this->userTeam[] = $userTeam;
            $userTeam->setTeam($this);
        }

        return $this;
    }

    public function getTeamApp(): Collection
    {
        return $this->teamApp;
    }

    public function removeUserTeam(UserTeam $userTeam): self
    {
        if ($this->userTeam->removeElement($userTeam)) {
            if ($userTeam->getTeam() === $this) {
                $userTeam->setTeam(null);
            }
        }

        return $this;
    }

    public function addTeamApp(TeamApp $teamApp): self
    {
        if (!$this->teamApp->contains($teamApp)) {
            $this->teamApp[] = $teamApp;
            $teamApp->setTeam($this);
        }

        return $this;
    }

    public function removeTeamApp(TeamApp $teamApp): self
    {
        if ($this->teamApp->removeElement($teamApp)) {
            if ($teamApp->getApp() === $this) {
                $teamApp->setTeam(null);
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

    public function toResponse(): TeamResponse
    {
        return (new TeamResponse())
            ->setId($this->id)
            ->setName($this->name)
            ->setUserTeam($this->userTeam->toArray())
            ->setTotalApp($this->teamApp->count())
            ->setApps($this->teamApp->toArray());
    }
}