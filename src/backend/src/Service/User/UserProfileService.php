<?php

namespace App\Service\User;

use App\Model\Request\UserRequest;
use App\Model\Response\Response;
use App\Repository\User\UserRepositoryInterface;
use App\Service\Auth\AuthServiceInterface;
use App\Service\BaseService;
use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserProfileService
    extends BaseService
    implements UserProfileServiceInterface
{
    private UserRepositoryInterface $userRepository;
    private UserPasswordHasherInterface $passwordHashes;
    private AuthServiceInterface $authService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        UserPasswordHasherInterface $passwordHashes,
        AuthServiceInterface $authService
    ) {
        $this->userRepository = $userRepository;
        $this->passwordHashes = $passwordHashes;
        $this->authService = $authService;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function update(UserRequest $userRequest): JsonResponse
    {
        // validate request
        $this->validate($userRequest, ['profile']);

        // initiate user
        $user = $this->getUser();
        $user->setEmail($userRequest->getEmail())
            ->setName($userRequest->getName());

        if ($userRequest->getPassword()) {
            $password = $this->passwordHashes
                ->hashPassword($user, $userRequest->getPassword());
            $user->setPassword($password);
        }

        $user->setModifiedAt(new DateTime());

        // save
        try {
            $this->userRepository->save($user);
        } catch (Exception $e) {
            throw new Exception("Update user profile was failed");
        }

        $response = $this->authService->generateNewToken($user);
        $response->setContent(
            json_encode(
                new Response(
                    true,
                    "Update user profile"
                )
            )
        );

        return $response;
    }
}