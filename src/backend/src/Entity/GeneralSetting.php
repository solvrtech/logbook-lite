<?php

namespace App\Entity;

use App\Model\Response\GeneralSettingResponse;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: '`general_setting`')]
class GeneralSetting implements ResponseEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $applicationSubtitle = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $languagePreference = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $defaultLanguage = null;

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

    public function getApplicationSubtitle(): ?string
    {
        return $this->applicationSubtitle;
    }

    public function setApplicationSubtitle(?string $applicationSubtitle): self
    {
        $this->applicationSubtitle = $applicationSubtitle;

        return $this;
    }

    public function getLanguagePreference(): ?string
    {
        return $this->languagePreference;
    }

    public function setLanguagePreference(?string $languagePreference): self
    {
        $this->languagePreference = $languagePreference;

        return $this;
    }

    public function getDefaultLanguage(): ?string
    {
        return $this->defaultLanguage;
    }

    public function setDefaultLanguage(?string $defaultLanguage): self
    {
        $this->defaultLanguage = $defaultLanguage;

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

    public function toResponse(): GeneralSettingResponse
    {
        return (new GeneralSettingResponse())
            ->setApplicationSubtitle($this->applicationSubtitle)
            ->setLanguagePreference($this->languagePreference)
            ->setDefaultLanguage($this->defaultLanguage);
    }
}