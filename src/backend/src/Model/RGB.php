<?php

namespace App\Model;

class RGB
{
    private ?int $red = null;
    private ?int $green = null;
    private ?int $blue = null;

    public function getRed(): ?int
    {
        return $this->red;
    }

    public function setRed(?int $red): self
    {
        $this->red = $red;

        return $this;
    }

    public function getBlue(): ?int
    {
        return $this->blue;
    }

    public function setBlue(?int $blue): self
    {
        $this->blue = $blue;

        return $this;
    }

    public function getString(): string
    {
        return $this->red.'-'.$this->getGreen().'-'.$this->blue;
    }

    public function getGreen(): ?int
    {
        return $this->green;
    }

    public function setGreen(?int $green): self
    {
        $this->green = $green;

        return $this;
    }
}