<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;

class HealthStatusSettingRequest
{
    #[Assert\Type('bool')]
    public ?bool $isEnabled = null;

    #[Assert\When(
        expression: 'this.isEnabled',
        constraints: [
            new Assert\NotBlank,
            new Assert\Url,
            new Assert\Length(max: 255),
        ]
    )]
    private ?string $url = null;

    #[Assert\When(
        expression: 'this.isEnabled',
        constraints: [
            new Assert\NotBlank,
            new Assert\Type('int'),
            new Assert\Range(min: 5, max: 259200),
        ]
    )]
    private ?int $period = null;

    #[Assert\When(
        expression: 'this.isEnabled',
        constraints: [
            new Assert\IsTrue(message: "Default language not valid"),
        ]
    )]
    public function isUrlValid(): bool
    {
        return !str_ends_with($this->url, '/');
    }

    public function getIsEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(?bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

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

    public function getPeriod(): ?int
    {
        return $this->period;
    }

    public function setPeriod(?int $period): self
    {
        $this->period = $period;

        return $this;
    }
}