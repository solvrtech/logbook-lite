<?php

namespace App\Repository\Team;

use App\Entity\Team;
use App\Entity\User;

interface UserTeamRepositoryInterface
{
    /**
     * Save user team association entity into storage.
     *
     * @param Team $team
     * @param array $users [User $user, string $role]
     * @param bool $update
     */
    public function bulkSave(Team $team, array $users, bool $update = false): void;

    /**
     * Remove the given user from any team.
     *
     * @param User $user
     */
    public function removeUser(User $user): void;
}