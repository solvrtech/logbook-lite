<?php

namespace App\Entity;

use App\Model\Response\SecuritySettingResponse;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: '`security_setting`')]
class SecuritySetting implements ResponseEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::INTEGER, length: 20)]
    private ?int $loginMaxFailed = null;

    #[ORM\Column(type: Types::INTEGER, length: 999)]
    private ?int $loginInterval = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $mfaAuthentication = null;

    #[ORM\Column(type: Types::INTEGER, length: 59, nullable: true)]
    private ?int $mfaDelayResend = null;

    #[ORM\Column(type: Types::INTEGER, length: 20, nullable: true)]
    private ?int $mfaMaxResend = null;

    #[ORM\Column(type: Types::INTEGER, length: 20, nullable: true)]
    private ?int $mfaMaxFailed = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTime $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getLoginMaxFailed(): ?int
    {
        return $this->loginMaxFailed;
    }

    public function setLoginMaxFailed(?int $loginMaxFailed): self
    {
        $this->loginMaxFailed = $loginMaxFailed;

        return $this;
    }

    public function getLoginInterval(): ?int
    {
        return $this->loginInterval;
    }

    public function setLoginInterval(?int $loginInterval): self
    {
        $this->loginInterval = $loginInterval;

        return $this;
    }

    public function getMfaAuthentication(): ?bool
    {
        return $this->mfaAuthentication;
    }

    public function setMfaAuthentication(?bool $mfaAuthentication): self
    {
        $this->mfaAuthentication = $mfaAuthentication;

        return $this;
    }

    public function getMfaDelayResend(): ?int
    {
        return $this->mfaDelayResend;
    }

    public function setMfaDelayResend(?int $mfaDelayResend): self
    {
        $this->mfaDelayResend = $mfaDelayResend;

        return $this;
    }

    public function getMfaMaxResend(): ?int
    {
        return $this->mfaMaxResend;
    }

    public function setMfaMaxResend(?int $mfaMaxResend): self
    {
        $this->mfaMaxResend = $mfaMaxResend;

        return $this;
    }

    public function getMfaMaxFailed(): ?int
    {
        return $this->mfaMaxFailed;
    }

    public function setMfaMaxFailed(?int $mfaMaxFailed): self
    {
        $this->mfaMaxFailed = $mfaMaxFailed;

        return $this;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function toResponse(): SecuritySettingResponse
    {
        return (new SecuritySettingResponse())
            ->setLoginMaxFailed($this->loginMaxFailed)
            ->setLoginInterval($this->loginInterval)
            ->setMfaAuthentication($this->mfaAuthentication)
            ->setMfaMaxFailed($this->mfaMaxFailed)
            ->setMfaMaxResend($this->mfaMaxResend)
            ->setMfaDelayResend($this->mfaDelayResend);
    }
}