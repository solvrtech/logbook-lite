<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;

class AppMailConfigRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $smtpHost = null;

    #[Assert\NotBlank]
    #[Assert\Type('int')]
    #[Assert\Length(max: 3)]
    private ?int $smtpPort = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private ?string $username = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $password = null;

    #[Assert\NotBlank]
    #[Assert\Choice(['none', 'ssl', 'tls'])]
    private ?string $encryption = null;

    #[Assert\NotBlank]
    #[Assert\Type('bool')]
    private ?bool $authentication = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    private ?string $fromEmail = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $fromName = null;

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

    public function getAuthentication(): ?bool
    {
        return $this->authentication;
    }

    public function setAuthentication(?bool $authentication): self
    {
        $this->authentication = $authentication;

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

}