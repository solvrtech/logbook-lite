<?php

namespace App\Model;

class Time
{
    private ?int $hour = 0;
    private ?int $minute = 0;
    private ?int $second = 0;

    public function getHour(): ?int
    {
        return $this->hour;
    }

    public function setHour(?int $hour): self
    {
        $this->hour = $hour;

        return $this;
    }

    public function getMinute(): ?int
    {
        return $this->minute;
    }

    public function setMinute(?int $minute): self
    {
        $this->minute = $minute;

        return $this;
    }

    public function getSecond(): ?int
    {
        return $this->second;
    }

    public function setSecond(?int $second): self
    {
        $this->second = $second;

        return $this;
    }
}