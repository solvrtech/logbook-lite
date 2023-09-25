<?php

namespace App\Repository\App;

use App\Entity\App;
use Doctrine\DBAL\Exception;

class PostgresTeamAppRepository extends SqlTeamAppRepository
{
    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function bulkSave(App $app, array $teams, bool $update = false): void
    {
        $i = 1;
        $totalUser = count($teams);
        $lastEnteredId = $this->connection
            ->executeQuery("SELECT max(id) from team_app")
            ->fetchOne();


        if ($update) {
            $this->bulkDelete($app->getId());
        }

        $query = "INSERT INTO team_app (id, app_id, team_id) VALUES ";
        foreach ($teams as $team) {
            $id = $lastEnteredId + $i;
            $query .= "({$id}, {$app->getId()}, {$team['teamId']})";

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
     * Delete all appTeams by app id.
     *
     * @param int $appId
     *
     * @return void
     *
     * @throws Exception
     */
    private function bulkDelete(int $appId): void
    {
        $query = "DELETE FROM team_app t WHERE t.app_id = {$appId}";

        $this->connection->executeQuery($query);
    }
}