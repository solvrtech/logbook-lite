<?php

namespace App\Model;

class Logo
{
    private ?string $initials = null;

    private ?RGB $rgb = null;

    private ?string $destination = null;
    private ?string $fontPath = null;

    public function getInitials(): ?string
    {
        return $this->initials;
    }

    public function setInitials(?string $initials): self
    {
        $this->initials = $initials;

        return $this;
    }

    public function getRgb(): ?RGB
    {
        return $this->rgb;
    }

    public function setRgb(?RGB $rgb): self
    {
        $this->rgb = $rgb;

        return $this;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function setDestination(?string $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    public function getFontPath(): ?string
    {
        return $this->fontPath;
    }

    public function setFontPath(?string $fontPath): self
    {
        $this->fontPath = $fontPath;

        return $this;
    }
}