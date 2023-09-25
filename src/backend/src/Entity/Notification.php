<?php

namespace App\Entity;

use App\Common\DateTimeHelper;
use App\Model\Response\NotificationResponse;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: '`notification`')]
class Notification implements ResponseEntityInterface
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

    #[ORM\OneToMany(mappedBy: 'notification', targetEntity: UserNotification::class, cascade: ['persist'])]
    private Collection $userNotification;

    #[ORM\Column(length: 256)]
    private ?string $message = null;

    #[ORM\Column(length: 256, nullable: true)]
    private ?string $link = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTime $createdAt = null;

    public function __construct()
    {
        $this->userNotification = new ArrayCollection();
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

    public function getUserNotification(): Collection
    {
        return $this->userNotification;
    }

    public function addUserNotification(UserNotification $userNotification): self
    {
        if (!$this->userNotification->contains($userNotification)) {
            $this->userNotification[] = $userNotification;
            $userNotification->setNotification($this);
        }

        return $this;
    }

    public function removeUserNotification(UserNotification $userNotification): self
    {
        if ($this->userNotification->removeElement($userNotification)) {
            if ($userNotification->getNotification() === $this) {
                $userNotification->setNotification(null);
            }
        }

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

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

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

    public function toResponse(): NotificationResponse
    {
        return (new NotificationResponse())
            ->setId($this->id)
            ->setApp($this->app)
            ->setMessage($this->message)
            ->setLink($this->link)
            ->setCreatedAt(
                (new DateTimeHelper())
                    ->dateTimeToStr($this->createdAt)
            );
    }
}