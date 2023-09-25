<?php

namespace App\Repository\Team;

use App\Entity\Team;
use Doctrine\DBAL\Exception;

class MysqlUserTeamRepository extends SqlUserTeamRepository
{
    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function bulkSave(Team $team, array $users, bool $update = false): void
    {
        $i = 1;
        $totalUser = count($users);
        $lastEnteredId = $this->connection
            ->executeQuery("SELECT max(id) from user_team")
            ->fetchOne();

        if ($update) {
            $this->bulkDelete($team->getId());
        }

        $query = "INSERT INTO user_team (id, team_id, user_id, role) VALUES ";
        foreach ($users as $user) {
            $id = $lastEnteredId + $i;
            $query .= "({$id}, {$team->getId()}, {$user['userId']}, '{$user['role']}')";

            if ($i === $totalUser) {
                $query .= ";";
            } else {
                $query .= ", ";
            }

            $i++;
        }

        $this->connection->executeQuery($query);
    }

    /**
     * Delete all userTeams by team id.
     *
     * @param int $teamId
     *
     * @return void
     *
     * @throws Exception
     */
    private function bulkDelete(int $teamId): void
    {
        $query = "DELETE FROM user_team t WHERE t.team_id = {$teamId}";

        $this->connection->executeQuery($query);
    }
}