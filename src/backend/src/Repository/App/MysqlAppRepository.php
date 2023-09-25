<?php

namespace App\Repository\App;

use App\Common\Config\TeamConfig;
use App\Common\Config\UserConfig;
use App\Common\QueryBuilder;
use App\Entity\User;

class MysqlAppRepository extends SqlAppRepository
{
    /**
     * {@inheritDoc}
     */
    public function findLogJoinQuery(QueryBuilder $queryBuilder, User $user): void
    {
        if (UserConfig::ROLE_ADMIN !== $user->getRole()) {
            $teamManager = TeamConfig::TEAM_MANAGER;
            $subQb = (new QueryBuilder())
                ->select('SELECT a_sub.id, t_sub.id AS team_id')
                ->table('app a_sub')
                ->join('team_app ta_sub on a_sub.id = ta_sub.app_id')
                ->join('team t_sub on t_sub.id = ta_sub.team_id')
                ->join('user_team ut_sub on t_sub.id = ut_sub.team_id')
                ->join('user u_sub on u_sub.id = ut_sub.user_id')
                ->where("u_sub.id = {$user->getId()}")
                ->where("ut_sub.role = '{$teamManager}'")
                ->build();

            $queryBuilder
                ->join('team_app ta on a.id = ta.app_id')
                ->join('team t on t.id = ta.team_id')
                ->join('user_team ut on t.id = ut.team_id')
                ->join('user u on u.id = ut.user_id')
                ->leftJoin("($subQb) tm ON a.id = tm.id")
                ->where("u.id = {$user->getId()}");
        }
    }
}