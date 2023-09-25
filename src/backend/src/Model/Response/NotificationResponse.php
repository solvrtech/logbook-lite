<?php

namespace App\Model\Response;

use App\Entity\App;

class NotificationResponse
{
    public ?int $id = null;
    public ?AppStandardResponse $app = null;
    public ?string $message = null;
    public ?string $link = null;
    public ?string $createdAt = null;

    public function getApp(): ?AppStandardResponse
    {
        return $this->app;
    }

    public function setApp(?App $app): self
    {
        $this->app = $app?->toStandardResponse();

        return $this;
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

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?string $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}