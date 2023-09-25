<?php

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TooManyLoginAttemptsException extends AuthenticationException
{
    private ?int $threshold;

    public function __construct(int $threshold = null)
    {
        $this->threshold = $threshold;
    }

    public function getMessageData(): array
    {
        return [
            'WaitingTimeInMinutes' => $this->threshold
        ];
    }

    public function getMessageKey(): string
    {
        return 'Too many failed login attempts.';
    }

    public function __serialize(): array
    {
        return [$this->threshold, parent::__serialize()];
    }

    public function __unserialize(array $data): void
    {
        [$this->threshold, $parentData] = $data;
        $parentData = \is_array($parentData) ? $parentData : unserialize($parentData);
        parent::__unserialize($parentData);
    }
}