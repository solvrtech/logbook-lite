<?php

namespace App\Validator;

use App\Repository\User\UserRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EmailUniqueValidator extends ConstraintValidator
{
    private UserRepositoryInterface $userRepository;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        UserRepositoryInterface $userRepository,
        TokenStorageInterface   $tokenStorage
    )
    {
        $this->userRepository = $userRepository;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@iheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();

        if ($this->userRepository->uniqueEmail($value, $user->getId()))
            $this->context->buildViolation($constraint->message)
                ->addViolation();
    }
}
