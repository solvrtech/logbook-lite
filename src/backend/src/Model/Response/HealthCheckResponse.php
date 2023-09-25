<?php

namespace App\Model\Response;

class HealthCheckResponse
{
    public ?string $checkKey = null;
    public ?string $status = null;
    public null|array|object $meta = null;

    public function getCheckKey(): ?string
    {
        return $this->checkKey;
    }

    public function setCheckKey(?string $checkKey): self
    {
        $this->checkKey = $checkKey;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getMeta(): array|object|null
    {
        return $this->meta;
    }

    public function setMeta(?string $meta): self
    {
        $this->meta = $meta ? json_decode($meta, true) : [];

        return $this;
    }
}