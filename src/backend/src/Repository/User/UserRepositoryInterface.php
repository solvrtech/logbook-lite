<?php

namespace App\Repository\User;

use App\Entity\User;
use App\Model\Pagination;
use App\Model\Request\SearchRequest;

interface UserRepositoryInterface
{
    /**
     *  Find all User entities matching the name or email with limiter.
     *
     * @param SearchRequest $searchRequest
     *
     * @return Pagination
     */
    public function findUser(SearchRequest $searchRequest): Pagination;

    /**
     * Find all User entities who registered on team matching with $appId.
     *
     * @param int $appId
     *
     * @return array
     */
    public function findUserByAppId(int $appId): array;

    /**
     * Find User entity matching with given $email.
     *
     * @param string $email
     *
     * @return User|null
     */
    public function findUserByEmail(string $email): User|null;

    /**
     * Find User entity matching with given $id
     *
     * @param string|int $id
     *
     * @return User|null
     */
    public function findUserById(string|int $id): User|null;

    /**
     * Find user who assigned to app matching given $appId and role team.
     *
     * @param int $appId
     * @param string $role
     *
     * @return array
     */
    public function findUserAssignedToApp(int $appId, string $role): array;

    /**
     * Find user who assigned to app matching given $appId and $userId.
     *
     * @param int $appId
     * @param int $userId
     *
     * @return User|null
     */
    public function findUserAssignedToAppByUserId(int $appId, int $userId): User|null;

    /**
     * Find all team role of the user matching with the given email and app id.
     *
     * @param string $email
     * @param int|null $appId
     *
     * @return array
     */
    public function findUserTeamRole(string $email, int|null $appId = null): array;

    /**
     * Check that the given user has a team and was assigned to the app.
     *
     * @param User $user
     *
     * @return array
     */
    public function isUserHasTeam(User $user): array;

    /**
     * Check that the given user is a team manager;
     * if the user is a team manager, return the list of the user's teams.
     *
     * @param User $user
     *
     * @return array
     */
    public function isTeamManager(User $user): array;

    /**
     * Find all standard user.
     *
     * @return array
     */
    public function findAllStandardUser(): array;

    /**
     * Find the total of all admin users.
     *
     * @return int
     */
    public function findTotalAdmin(): int;

    /**
     * Save the User entity into storage.
     *
     * @param User $user
     */
    public function save(User $user): void;

    /**
     * Remove the User entity from the searching result list.
     *
     * @param User $user
     */
    public function softDelete(User $user): void;

    /**
     * Check email already registered.
     *
     * @param string $email
     * @param int|null $id
     *
     * @return bool
     */
    public function uniqueEmail(string $email, int|null $id = null): bool;
}
