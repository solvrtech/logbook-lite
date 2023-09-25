<?php

namespace App\Model\Response;

class GeneralSettingResponse
{
    public ?string $applicationSubtitle = null;
    public ?array $languagePreference = null;
    public ?string $defaultLanguage = null;

    public function getApplicationSubtitle(): ?string
    {
        return $this->applicationSubtitle;
    }

    public function setApplicationSubtitle(?string $applicationSubtitle): self
    {
        $this->applicationSubtitle = $applicationSubtitle;

        return $this;
    }

    public function getLanguagePreference(): ?array
    {
        return $this->languagePreference;
    }

    public function setLanguagePreference(?string $languagePreference): self
    {
        $this->languagePreference = json_decode($languagePreference);

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