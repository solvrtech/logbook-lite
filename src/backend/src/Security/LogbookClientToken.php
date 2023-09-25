<?php

namespace App\Security;

use InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\User\UserInterface;

class LogbookClientToken extends AbstractToken
{
    private string $firewallName;

    public function __construct(UserInterface $user, string $firewallName, mixed $attribute, array $roles)
    {
        parent::__construct($roles);

        if ('' === $firewallName)
            throw new InvalidArgumentException(
                '$firewallName must not be empty.'
            );

        $this->setUser($user);
        $this->setAttributes($attribute);
        $this->firewallName = $firewallName;
    }

    public function getFirewallName(): string
    {
        return $this->firewallName;
    }

    /**
     * {@inheritdoc}
     */
    public function __serialize(): array
    {
        return [$this->firewallName, parent::__serialize()];
    }

    /**
     * {@inheritdoc}
     */
    public function __unserialize(array $data): void
    {
        [$this->firewallName, $parentData] = $data;
        parent::__unserialize($parentData);
    }
}