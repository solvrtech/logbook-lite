<?php

namespace App\Security\Exception;

use App\Common\Config\MFAConfig;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TooManyMFAAttemptsException
    extends AuthenticationException
    implements TooManyMFAAttemptsExceptionInterface
{
    private ?int $threshold;
    private string $action = MFAConfig::MFA_ATTEMPT;

    public function __construct(?int $threshold = null, ?string $action = null)
    {
        $this->threshold = $threshold;
        if ($action)
            $this->action = $action;
    }

    public function getMessageData(): array
    {
        return [
            'WaitingTimeInMinutes' => $this->threshold
        ];
    }

    public function getMessageKey(): string
    {
        return "Too many failed 2fa {$this->action}.";
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