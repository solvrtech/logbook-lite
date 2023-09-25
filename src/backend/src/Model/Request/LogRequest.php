<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;

class LogRequest
{
    #[Assert\NotBlank]
    private ?string $message = null;

    private ?string $file = null;

    private ?array $stackTrace = null;

    #[Assert\NotBlank]
    #[Assert\Type('int')]
    private ?int $code = null;

    #[Assert\NotBlank]
    private ?string $level = null;

    #[Assert\NotBlank]
    private ?string $channel = null;

    #[Assert\NotBlank]
    private ?string $datetime = null;

    #[Assert\Type('array')]
    private ?array $additional = null;

    #[Assert\NotBlank]
    #[Assert\Type('array')]
    #[Assert\Collection(
        fields: [
            'url' => [],
            'server' => [],
            'httpMethod' => [],
            'ip' => [],
            'userAgent' => [],
        ],
    )]
    private ?array $client = null;

    #[Assert\IsTrue(message: "StackTrace must be an array.")]
    public function isStackTraceRegistered(): bool
    {
        if ($this->stackTrace) {
            return is_array($this->stackTrace);
        }

        return true;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getStackTrace(): ?string
    {
        $stackTrace = $this->stackTrace ?: [];

        return json_encode($stackTrace);
    }

    public function setStackTrace(?array $stackTrace): self
    {
        $this->stackTrace = $stackTrace;

        return $this;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(?int $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(?string $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function setChannel(?string $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    public function getDatetime(): ?string
    {
        return $this->datetime;
    }

    public function setDatetime(?string $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }

    public function getAdditional(): ?string
    {
        $additional = $this->additional ?: [];

        return json_encode($additional);
    }

    public function setAdditional(?array $additional): void
    {
        $this->additional = $additional;
    }

    public function getClient(): array
    {
        return $this->client ?: [];
    }

    public function setClient(?array $client): self
    {
        $this->client = $client;

        return $this;
    }
}