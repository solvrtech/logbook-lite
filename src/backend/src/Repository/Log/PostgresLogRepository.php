<?php

namespace App\Repository\Log;

use App\Common\Config\UserConfig;
use App\Common\QueryBuilder;
use App\Entity\User;
use App\Model\Request\LogSearchRequest;

class PostgresLogRepository extends SqlLogRepository
{
    /**
     * {@inhertiDoc}
     */
    public function findLogJoinQuery(QueryBuilder $queryBuilder, User $user): void
    {
        $queryBuilder
            ->leftJoin('"user" ass ON ass.id = l.assignee')
            ->join("app a ON a.id = l.app_id")
            ->leftJoin("app_logo al ON al.app_id = a.id");

        if (UserConfig::ROLE_ADMIN !== $user->getRole()) {
            $queryBuilder
                ->join("team_app ta ON a.id = ta.app_id")
                ->join("team t ON t.id = ta.team_id")
                ->join("user_team ut ON t.id = ut.team_id")
                ->join('"user" u ON u.id = ut.user_id')
                ->where("u.id = {$user->getId()}");
        }
    }

    /**
     * {@inheritDoc}
     */
    public function findLogSelect(QueryBuilder $queryBuilder, string $order, int $offset, int $limit): void
    {
        $queryBuilder
            ->select(
                "SELECT DISTINCT l.id, l.message, l.level, l.date_time, l.instance_id, l.status, a.name AS app, a.type AS app_type, al.public_path as app_logo, ass.name AS assignee"
            )
            ->order("l.date_time {$order}, l.id {$order}")
            ->limit("LIMIT {$limit} OFFSET {$offset}");
    }

    /**
     * {@inheritDoc }
     */
    public function findLogFilter(QueryBuilder $queryBuilder, LogSearchRequest $request, ?int $appId = null): void
    {
        if ($request->getSearch()) {
            $queryBuilder
                ->where(
                    "(LOWER(l.message) LIKE '%{$request->getSearch()}%' OR LOWER(l.stack_trace) LIKE '%{$request->getSearch()}%')"
                );
        }

        if ($request->getLevel()) {
            $queryBuilder
                ->where("LOWER(l.level) = '{$request->getLevel()}'");
        }

        if ($request->getStatus()) {
            $queryBuilder
                ->where("l.status = '{$request->getStatus()}'");
        }

        if ($request->getTag()) {
            $queryBuilder
                ->where("l.tag::jsonb @> '{$request->getTag()}'::jsonb");
        }

        if ($request->getStartDateTime() && $request->getEndDateTime()) {
            $queryBuilder
                ->where("l.date_time >= '{$request->getStartDateTime()}'")
                ->where("l.date_time <= '{$request->getEndDateTime()}'");
        }

        if ($request->getAssignee()) {
            $queryBuilder
                ->where("ass.id = {$request->getAssignee()}");
        }

        if ($appId) {
            $queryBuilder
                ->where("a.id = {$appId}");
        } else {
            if ($request->getApp()) {
                $queryBuilder
                    ->where("a.id = {$request->getApp()}");
            }
        }
    }
}
