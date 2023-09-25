<?php

namespace App\Entity;

use App\Model\Response\UserResponse;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: '`user`')]
class User
    implements UserInterface, PasswordAuthenticatedUserInterface, ResponseEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private ?string $role = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\OneToOne(
        mappedBy: 'user',
        targetEntity: UserMFASetting::class,
        cascade: ['persist']
    )]
    private ?UserMFASetting $userMFASetting = null;

    #[ORM\OneToMany(
        mappedBy: 'user',
        targetEntity: UserTeam::class,
        cascade: ['persist']
    )]
    private Collection $userTeam;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTime $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $modifiedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $deletedAt = null;

    public function __construct()
    {
        $this->userTeam = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

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

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return [$this->role];
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getUserMFASetting(): ?UserMFASetting
    {
        return $this->userMFASetting;
    }

    public function setUserMFASetting(?UserMFASetting $userMFASetting): self
    {
        // unset the owning side of the relation if necessary
        if ($userMFASetting === null && $this->userMFASetting !== null) {
            $this->userMFASetting->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($userMFASetting !== null && $userMFASetting->getUser() !== $this) {
            $userMFASetting->setUser($this);
        }

        $this->userMFASetting = $userMFASetting;

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
            $userTeam->setUser($this);
        }

        return $this;
    }

    public function removeUserTeam(UserTeam $userTeam): self
    {
        if ($this->userTeam->removeElement($userTeam)) {
            if ($userTeam->getUser() === $this) {
                $userTeam->setUser(null);
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

    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTime $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function toResponse(): UserResponse
    {
        return (new UserResponse())
            ->setId($this->id)
            ->setEmail($this->email)
            ->setMfa($this->userMFASetting)
            ->setName($this->name)
            ->setRole($this->role);
    }
}
