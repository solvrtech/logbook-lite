<?php

namespace App\Repository\Health;

use App\Common\Config\HealthStatusConfig;
use App\Common\Config\UserConfig;
use App\Common\QueryBuilder;
use App\Entity\User;

class MysqlHealthStatusRepository extends SqlHealthStatusRepository
{
    /**
     * {@inheritDoc}
     */
    public function findHealthStatusJoin(QueryBuilder $queryBuilder, User $user): void
    {
        $queryBuilder
            ->join("app a ON a.id = hs.app_id")
            ->leftJoin("app_logo al ON al.app_id = a.id");

        if (UserConfig::ROLE_ADMIN !== $user->getRole()) {
            $queryBuilder
                ->join("team_app ta ON a.id = ta.app_id")
                ->join("team t ON t.id = ta.team_id")
                ->join("user_team ut ON t.id = ut.team_id")
                ->join("user u ON u.id = ut.user_id")
                ->where("u.id = {$user->getId()}");
        }
    }

    /**
     * {@inheritDoc}
     */
    public function findHealthStatusSelect(
        QueryBuilder $queryBuilder,
        string $order,
        int $offset,
        int $limit,
    ): void {
        $failed = HealthStatusConfig::FAILED;
        $queryTotalFiled = (new QueryBuilder())
            ->select("SELECT COUNT(hc.id)")
            ->table('health_check hc')
            ->join("health_status hs1 ON hs1.id = hc.health_status_id")
            ->where("hs1.id = hs.id")
            ->where("hc.status = '{$failed}'")
            ->build();

        $queryBuilder
            ->select(
                "SELECT DISTINCT hs.id, hs.status, hs.created_at, ({$queryTotalFiled}) as total_failed, a.name AS app, a.type AS app_type, al.public_path as app_logo"
            )
            ->order("hs.id {$order}")
            ->limit("LIMIT {$offset}, {$limit}");
    }

    /**
     * {@inheritDoc}
     */
    public function appHealthHasFailedStatus(int $appId, int $limit): array
    {
        $query = (new QueryBuilder())
            ->select(
                "SELECT MAX(CASE WHEN hs.status = 'ok' THEN hs.created_at ELSE NULL END) AS last_active, COUNT(CASE WHEN hs.status = 'failed' AND DATE(hs.created_at) = CURDATE() THEN hs.id ELSE NULL END) AS has_failed_status"
            )
            ->table("health_status hs")
            ->where("hs.app_id = :appId")
            ->build();

        $params = [
            'appId' => $appId,
        ];
        $result = $this->connection
            ->executeQuery($query, $params)
            ->fetchAssociative();

        return [
            'lastActive' => $result['last_active'],
            'hasFailedStatus' => $result['has_failed_status'] > $limit,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function appHealthHasIssue(int $appId, array $alertConfig): bool
    {
        $query = (new QueryBuilder())
            ->select("SELECT COUNT(DISTINCT hs.id) as hasIssue")
            ->table("health_status hs")
            ->join("app a ON hs.app_id = a.id")
            ->join("health_check hc on hs.id = hc.health_status_id")
            ->where("a.id = :appId")
            ->where("hs.status = :status")
            ->where("DATE(hs.created_at) = CURDATE()")
            ->where($this->createAlertSpecificQuery($alertConfig, 'hc'))
            ->build();

        $params = [
            'appId' => $appId,
            'status' => 'ok',
        ];
        $stmt = $this->connection->prepare($query);
        $resultSet = $stmt->executeQuery($params);

        return 0 < $resultSet->fetchOne();
    }

    /**
     * {@inheriDoc}
     */
    public function createAlertSpecificQuery(array $specificConfig, string $table): string
    {
        $conditions = [];

        foreach ($specificConfig as $config) {
            switch ($config['item']) {
                case 'status':
                    $condition = "{$table}.status = '{$config['value']}'";
                    break;
                case 'usedDiskSpace':
                    $condition = "JSON_EXTRACT({$table}.meta, '$.usedDiskSpace') >= {$config['value']}";
                    break;
                case 'memoryUsage':
                    $condition = "JSON_EXTRACT({$table}.meta, '$.memoryUsage') >= {$config['value']}";
                    break;
                case 'database':
                    $condition = "JSON_EXTRACT({$table}.meta, '$.databaseSize.*') >= {$config['value']}";
                    break;
                case 'lastMinute':
                    $condition = "JSON_EXTRACT({$table}.meta, '$.cpuLoad.lastMinute') >= {$config['value']}";
                    break;
                case 'last5minutes':
                    $condition = "JSON_EXTRACT({$table}.meta, '$.cpuLoad.last5minutes') >= {$config['value']}";
                    break;
                case 'last15Minutes':
                    $condition = "JSON_EXTRACT({$table}.meta, '$.cpuLoad.last15Minutes') >= {$config['value']}";
                    break;
                default:
                    continue 2; // skip iteration if item is not recognized
            }
            $conditions[] = "({$table}.check_key = '{$config['checkKey']}' AND {$condition})";
        }

        return '('.implode(' OR ', $conditions).')';
    }
}
