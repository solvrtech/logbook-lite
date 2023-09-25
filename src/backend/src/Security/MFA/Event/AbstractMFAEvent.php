<?php

namespace App\Security\MFA\Event;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractMFAEvent extends Event
{
    private UserInterface $user;
    private string $MFAMethod;

    public function __construct(
        UserInterface $user,
        string        $MFAMethod
    )
    {
        $this->user = $user;
        $this->MFAMethod = $MFAMethod;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getMFAMethod(): string
    {
        return $this->MFAMethod;
    }
}