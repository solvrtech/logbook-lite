<?php

namespace App\Model;

use DateTime;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class AlertNotification
{
    private ?int $appId = null;
    private ?int $alertId = null;
    private ?string $message = null;
    private ?string $url = null;
    private ?DateTime $createdAt = null;
    private ?TemplatedEmail $emailTemplate = null;
    private ?string $notifyTo = null;

    public function getAppId(): ?int
    {
        return $this->appId;
    }

    public function setAppId(?int $appId): self
    {
        $this->appId = $appId;

        return $this;
    }

    public function getAlertId(): ?int
    {
        return $this->alertId;
    }

    public function setAlertId(?int $alertId): self
    {
        $this->alertId = $alertId;

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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

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

    public function getEmailTemplate(): ?TemplatedEmail
    {
        return $this->emailTemplate;
    }

    public function setEmailTemplate(?TemplatedEmail $emailTemplate): self
    {
        $this->emailTemplate = $emailTemplate;

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
}