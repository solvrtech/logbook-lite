<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;

class MailSettingRequest
{
    #[Assert\NotBlank(groups: ['create', 'test'])]
    #[Assert\Length(max: 255, groups: ['create', 'test'])]
    private ?string $smtpHost = null;

    #[Assert\NotBlank(groups: ['create', 'test'])]
    #[Assert\Type('int', groups: ['create', 'test'])]
    #[Assert\Length(max: 3, groups: ['create', 'test'])]
    private ?int $smtpPort = null;

    #[Assert\NotBlank(groups: ['create', 'test'])]
    #[Assert\Length(max: 50, groups: ['create', 'test'])]
    private ?string $username = null;

    #[Assert\Length(max: 255, groups: ['create', 'test'])]
    private ?string $password = null;

    #[Assert\NotBlank(groups: ['create', 'test'])]
    #[Assert\Choice(['none', 'ssl', 'tls'], groups: ['create', 'test'])]
    private ?string $encryption = null;

    #[Assert\NotBlank(groups: ['create', 'test'])]
    #[Assert\Email(groups: ['create', 'test'])]
    #[Assert\Length(max: 255, groups: ['create', 'test'])]
    private ?string $fromEmail = null;

    #[Assert\NotBlank(groups: ['create', 'test'])]
    #[Assert\Length(max: 255, groups: ['create', 'test'])]
    private ?string $fromName = null;

    #[Assert\NotBlank(groups: ['test'])]
    #[Assert\Email(groups: ['test'])]
    #[Assert\Length(max: 255, groups: ['test'])]
    private ?string $testEmail = null;

    #[Assert\NotBlank(groups: ['create'])]
    private ?string $token = null;

    public function getSmtpHost(): ?string
    {
        return $this->smtpHost;
    }

    public function setSmtpHost(?string $smtpHost): self
    {
        $this->smtpHost = $smtpHost;

        return $this;
    }

    public function getSmtpPort(): ?int
    {
        return $this->smtpPort;
    }

    public function setSmtpPort(?int $smtpPort): self
    {
        $this->smtpPort = $smtpPort;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEncryption(): ?string
    {
        return $this->encryption;
    }

    public function setEncryption(?string $encryption): self
    {
        $this->encryption = $encryption;

        return $this;
    }

    public function getFromEmail(): ?string
    {
        return $this->fromEmail;
    }

    public function setFromEmail(?string $fromEmail): self
    {
        $this->fromEmail = $fromEmail;

        return $this;
    }

    public function getFromName(): ?string
    {
        return $this->fromName;
    }

    public function setFromName(?string $fromName): self
    {
        $this->fromName = $fromName;

        return $this;
    }

    public function getTestEmail(): ?string
    {
        return $this->testEmail;
    }

    public function setTestEmail(?string $testEmail): self
    {
        $this->testEmail = $testEmail;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }
}