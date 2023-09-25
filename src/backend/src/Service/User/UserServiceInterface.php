<?php

namespace App\Service\User;

use App\Entity\User;
use App\Model\Pagination;
use App\Model\Request\SearchRequest;
use App\Model\Request\UserRequest;
use App\Model\Response\UserResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

interface UserServiceInterface
{
    /**
     * Search users matching the given $request from storage.
     *
     * @param SearchRequest $request
     *
     * @return Pagination
     */
    public function searchUser(SearchRequest $request): Pagination;

    /**
     * Search users who registered on team matching the given $appId and $request from storage.
     *
     * @return array
     */
    public function searchUserByAppId(): array;

    /**
     * Retrieve all standard users to assign to the team as member.
     *
     * @return array
     */
    public function getAllStandardUsers(): array;

    /**
     * Retrieve User matching the given $email from storage.
     *
     * @param string $email The user's email
     *
     * @return User|null
     */
    public function getUserByEmail(string $email): User|null;

    /**
     * Retrieve User matching the given $id from storage.
     *
     * @param int $id The user's id
     *
     * @return User
     */
    public function getUserById(int $id): User;

    /**
     * Retrieve user matching the given $appId and $userId from storage.
     *
     * @param int $appId
     * @param int $userId
     *
     * @return User
     */
    public function getUserByAppIdAndUserid(int $appId, int $userId): User;

    /**
     * Retrieve the currently authenticated user.
     *
     * @return UserResponse
     */
    public function currentUser(): UserResponse;

    /**
     * Retrieve all team role of the user matching with given user email and app ID.
     *
     * @param string $email
     * @param int|null $appId
     *
     * @return array
     */
    public function getUserTeamRole(string $email, int|null $appId = null): array;

    /**
     * Check that the given user has a team and was assigned to the app.
     *
     * @param User $user
     *
     * @return array
     */
    public function isUserHasTeam(User $user): array;

    /**
     * Is allowed to delete the user.
     * If the user is a team manager, return the app name assigned to the user's team
     * If the user is admin, return the total number of active admins.
     *
     * @param User|null $user
     * @param int|null $userId
     *
     * @return array
     */
    public function isAllowedToDelete(User|null $user = null, int|null $userId = null): array;

    /**
     * Save new User into storage.
     *
     * @param UserRequest $userRequest
     */
    public function create(UserRequest $userRequest): void;

    /**
     * Update User's data matching the given $id.
     *
     * @param UserRequest $userRequest
     * @param int $id
     *
     * @return JsonResponse
     */
    public function update(UserRequest $userRequest, int $id): JsonResponse;

    /**
     * Remove User matching the given $id from searching result.
     *
     * @param int $id
     *
     * @return bool
     */
    public function softDelete(int $id): bool;
}
