<?php

namespace App\Service\User;

use App\Common\Config\MFAConfig;
use App\Common\Config\UserConfig;
use App\Entity\User;
use App\Entity\UserMFASetting;
use App\Exception\ApiException;
use App\Model\Pagination;
use App\Model\Request\SearchRequest;
use App\Model\Request\UserRequest;
use App\Model\Response\Response;
use App\Model\Response\UserResponse;
use App\Repository\Team\UserTeamRepositoryInterface;
use App\Repository\User\UserRepositoryInterface;
use App\Service\Auth\AuthServiceInterface;
use App\Service\BaseService;
use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
    extends BaseService
    implements UserServiceInterface
{
    private UserRepositoryInterface $userRepository;
    private AuthServiceInterface $authService;
    private UserPasswordHasherInterface $passwordHashes;
    private UserTeamRepositoryInterface $userTeamRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        AuthServiceInterface $authService,
        UserPasswordHasherInterface $passwordHashes,
        UserTeamRepositoryInterface $userTeamRepository
    ) {
        $this->userRepository = $userRepository;
        $this->authService = $authService;
        $this->passwordHashes = $passwordHashes;
        $this->userTeamRepository = $userTeamRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function searchUser(SearchRequest $request): Pagination
    {
        // validate request
        $this->validate($request);

        return $this->userRepository->findUser($request);
    }

    /**
     * {@inheritDoc}
     */
    public function searchUserByAppId(): array
    {
        $appId = $this->tokenStorage()->getToken()->getAttribute('appId');
        $result = $this->userRepository->findUserByAppId($appId);

        return array_map(function (array $user) {
            return [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];
        }, $result);
    }

    /**
     * {@inheritDoc}
     */
    public function currentUser(): UserResponse
    {
        $user = $this->getUser();
        $userResponse = $user->toResponse();
        $userResponse
            ->setAssigned(self::isUserHasTeam($user));

        return $userResponse;
    }

    /**
     * {@inhertiDoc}
     */
    public function isUserHasTeam(User $user): array
    {
        if (UserConfig::ROLE_ADMIN === $user->getRole()) {
            return [
                'team' => 1,
                'app' => 1,
            ];
        }

        return $this->userRepository->isUserHasTeam($user);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function create(UserRequest $userRequest): void
    {
        // validate request
        $this->validate($userRequest, ['create']);

        if ($this->userRepository->uniqueEmail($userRequest->getEmail())) {
            throw new ApiException(
                "Email is already registered",
                [
                    'duplicatedEmail' => true,
                ]
            );
        }

        // initiate user
        $datetime = new DateTime();
        $user = (new User)
            ->setEmail($userRequest->getEmail())
            ->setName($userRequest->getName())
            ->setRole($userRequest->getRole())
            ->setUserMFASetting(
                (new UserMFASetting())
                    ->setMethod(MFAConfig::EMAIL_AUTHENTICATION)
                    ->setModifiedAt($datetime)
            );

        $password = $this->passwordHashes
            ->hashPassword($user, $userRequest->getPassword());
        $user->setPassword($password);
        $user->setCreatedAt($datetime);

        // save
        try {
            $this->userRepository->save($user);
        } catch (Exception $e) {
            throw new Exception("Create new user was failed");
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function update(UserRequest $userRequest, int $id): JsonResponse
    {
        // validate request
        $this->validate($userRequest, ['update']);

        if ($this->userRepository->uniqueEmail($userRequest->getEmail(), $id)) {
            throw new BadRequestException("Email is already registered");
        }

        // initiate user
        $user = $this->getUserById($id);
        $isLoggedUser = $user === $this->getUser();
        $user->setEmail($userRequest->getEmail())
            ->setName($userRequest->getName())
            ->setRole($userRequest->getRole());

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
            throw new Exception("Update user was failed");
        }

        $response = self::generateNewAccessToken($user, $isLoggedUser);
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

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function getUserById(int $id): User
    {
        $user = $this->userRepository->findUserById($id);

        if (null === $user) {
            throw new Exception("User not found");
        }

        return $user;
    }

    /**
     * Generate new access token when logged user update his data.
     *
     * @param User $user
     * @param bool $isLoggedUser
     *
     * @return JsonResponse
     */
    private function generateNewAccessToken(User $user, bool $isLoggedUser): JsonResponse
    {
        if ($isLoggedUser) {
            return $this->authService->generateNewToken($user);
        }

        return new JsonResponse();
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function getUserByAppIdAndUserid(int $appId, int $userId): User
    {
        $user = $this->userRepository->findUserAssignedToAppByUserId(
            $appId,
            $userId
        );

        if (null === $user) {
            throw new Exception("User not found");
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function getUserByEmail(string $email): User|null
    {
        return $this->userRepository->findUserByEmail($email);
    }

    /**
     * {@inheritDoc}
     */
    public function getUserTeamRole(string $email, int|null $appId = null): array
    {
        return $this->userRepository->findUserTeamRole($email, $appId);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllStandardUsers(): array
    {
        return $this->userRepository->findAllStandardUser();
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function softDelete(int $id): bool
    {
        $user = $this->userRepository->findUserById($id);
        $allowedToDelete = self::isAllowedToDelete($user);

        if (!$allowedToDelete['allowedToDelete']) {
            throw new Exception("Delete user was not allowed");
        }

        // soft delete
        try {
            $this->userTeamRepository->removeUser($user);
            $this->userRepository->softDelete($user);
        } catch (Exception $e) {
            throw new Exception("Delete user was failed");
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isAllowedToDelete(User|null $user = null, int|null $userId = null): array
    {
        if (null === $user) {
            $user = $this->userRepository->findUserById($userId);
        }


        if (UserConfig::ROLE_ADMIN === $user->getRole()) {
            $totalAdmin = $this->userRepository->findTotalAdmin();

            return [
                'allowedToDelete' => 1 < $totalAdmin,
                'totalAdmin' => $totalAdmin,
            ];
        }

        $teams = $this->userRepository->isTeamManager($user);

        return [
            'allowedToDelete' => 0 === count($teams),
            'teams' => $teams,
        ];
    }
}
