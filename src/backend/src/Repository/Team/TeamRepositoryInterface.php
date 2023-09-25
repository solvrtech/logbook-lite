<?php

namespace App\Repository\Team;

use App\Entity\Team;
use App\Entity\User;
use App\Model\Pagination;
use App\Model\Request\SearchRequest;

interface TeamRepositoryInterface
{
    /**
     * Find all Team entities matching the search with limiter.
     *
     * @param User $user
     * @param SearchRequest $searchRequest
     *
     * @return Pagination
     */
    public function findTeam(User $user, SearchRequest $searchRequest): Pagination;

    /**
     * Get all teams.
     *
     * @return array
     */
    public function getAll(): array;

    /**
     * Find Team entity matching with the given $id.
     *
     * @param User $user
     * @param int $id
     *
     * @return Team|null
     */
    public function findTeamById(User $user, int $id): Team|null;

    /**
     * Get user role on the team matching with given user id and team id
     *
     * @param int $userId
     * @param int $teamId
     *
     * @return string
     */
    public function getTeamRoleOfUser(int $userId, int $teamId): string;

    /**
     * Save Team entity into storage.
     *
     * @param Team $team
     */
    public function save(Team $team): void;

    /**
     * Delete Team entity from the storage.
     *
     * @param Team $team
     */
    public function delete(Team $team): void;
}