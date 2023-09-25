<?php

namespace App\Model\Request;

use App\Validator as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

class GeneralSettingRequest
{
    #[Assert\Length(max: 25)]
    private ?string $applicationSubtitle = null;

    #[Assert\NotBlank]
    #[Assert\Type('array')]
    #[AppAssert\LanguageExist]
    private ?array $languagePreference = null;

    #[Assert\NotBlank]
    #[AppAssert\LanguageExist]
    private ?string $defaultLanguage = null;

    #[Assert\IsTrue(message: "Default language not valid")]
    public function isDefaultLanguageValid(): bool
    {
        return in_array($this->defaultLanguage, $this->languagePreference);
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
        return json_encode($this->languagePreference);
    }

    public function setLanguagePreference(?array $languagePreference): self
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
}