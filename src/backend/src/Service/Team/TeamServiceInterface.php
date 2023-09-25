<?php

namespace App\Service\Team;

use App\Entity\Team;
use App\Model\Pagination;
use App\Model\Request\SearchRequest;
use App\Model\Request\TeamRequest;

interface TeamServiceInterface
{
    /**
     * Search Teams matching the given $request from storage.
     *
     * @param SearchRequest $request
     *
     * @return Pagination
     */
    public function searchTeam(SearchRequest $request): Pagination;

    /**
     * Retrieve all teams from storage.
     *
     * @return array
     */
    public function getAllTeam(): array;

    /**
     * Retrieve Team matching the given $id from storage.
     *
     * @param int $id The id of team
     *
     * @return Team
     */
    public function getTeamById(int $id): Team;

    /**
     * Retrieve role of the use on the team matching with the given user id and team id.
     *
     * @param int $userId
     * @param int $teamId
     *
     * @return string|null
     */
    public function getUserRoleOnTeam(int $userId, int $teamId): string|null;

    /**
     * Save new User into storage.
     *
     * @param TeamRequest $request
     */
    public function create(TeamRequest $request): void;

    /**
     * Update Team's data matching the given $id.
     *
     * @param TeamRequest $request
     * @param int $id
     */
    public function update(TeamRequest $request, int $id): void;

    /**
     * Remove Team matching the given $id from storage.
     *
     * @param int $id
     */
    public function delete(int $id): void;
}