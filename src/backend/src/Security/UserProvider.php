<?php

namespace App\Security;

use App\Entity\User;
use App\Service\User\UserServiceInterface;
use Exception;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Contracts\Service\Attribute\Required;

class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    #[Required]
    public UserServiceInterface $userServiceInterface;

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->userServiceInterface->getUserByEmail($identifier);

        if (null === $user)
            throw new Exception("Invalid credentials!");

        return $user;
    }

    /**
     * {@inheritDoc}
     *
     * @throws UserNotFoundException
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User)
            throw new UnsupportedUserException(
                sprintf('Invalid user class "%s".', get_class($user))
            );

        throw new UserNotFoundException();
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass(string $class): bool
    {
        return $class === User::class ||
            is_subclass_of($class, User::class);
    }

    /**
     * {@inheritDoc}
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
    }
}
