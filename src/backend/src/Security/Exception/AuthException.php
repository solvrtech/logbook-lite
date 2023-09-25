<?php

namespace App\Security\Exception;

use App\Exception\ApiExceptionInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthException
    extends AuthenticationException
    implements ApiExceptionInterface
{
    private string $messageKey;
    private array $messageData;

    public function __construct(
        string $messageKey = 'An authentication exception occurred.',
        array $messageData = []
    ) {
        $this->messageKey = $messageKey;
        $this->messageData = $messageData;
    }

    /**
     * {@inheritDoc}
     */
    public function getMessageData(): array
    {
        return $this->messageData;
    }

    public function getMessageKey(): string
    {
        return $this->messageKey;
    }

    public function __serialize(): array
    {
        return [parent::__serialize()];
    }

    public function __unserialize(array $data): void
    {
        [$parentData] = $data;
        $parentData = \is_array($parentData) ? $parentData : unserialize($parentData);
        parent::__unserialize($parentData);
    }
}