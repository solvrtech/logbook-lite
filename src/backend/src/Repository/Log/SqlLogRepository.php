<?php

namespace App\Repository\Log;

use App\Common\CommonHelper;
use App\Common\Config\CommonConfig;
use App\Common\DateTimeHelper;
use App\Common\QueryBuilder as CommonQueryBuilder;
use App\Entity\SqlLog;
use App\Entity\User;
use App\Model\Pagination;
use App\Model\Request\LogSearchRequest;
use App\Service\Search\SearchResultServiceInterface;
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

abstract class SqlLogRepository
    extends ServiceEntityRepository
    implements LogRepositoryInterface
{
    public SearchResultServiceInterface $resultService;
    public Connection $connection;

    public function __construct(
        SearchResultServiceInterface $resultService,
        Connection $connection,
        ManagerRegistry $registry
    ) {
        $this->resultService = $resultService;
        $this->connection = $connection;

        parent::__construct($registry, SqlLog::class);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function findLog(User $user, LogSearchRequest $searchRequest, int|null $appId = null): Pagination
    {
        $queryBuilder = (new CommonQueryBuilder())->table("log l");
        $this->findLogJoinQuery($queryBuilder, $user);

        $this->findLogFilter($queryBuilder, $searchRequest, $appId);
        $totalItems = $this->connection
            ->executeQuery(
                $queryBuilder
                    ->select("SELECT count(DISTINCT l.id)")
                    ->build()
            )
            ->fetchOne();
        $order = CommonConfig::ORDER;
        $this->findLogSelect($queryBuilder, $order, $searchRequest->offset(), $searchRequest->getSize());

        return $this->resultService->paginationResult(
            (new CommonHelper())->arraySnakeToCamelCase(
                $this->connection->fetchAllAssociative($queryBuilder->build())
            ),
            $totalItems,
            $searchRequest->getPage(),
            $searchRequest->getSize()
        );
    }

    /**
     * Builds the JOIN query of the SQL query
     *
     * @param CommonQueryBuilder $queryBuilder
     * @param User $user
     */
    abstract public function findLogJoinQuery(CommonQueryBuilder $queryBuilder, User $user): void;

    /**
     * Builds the WHERE clause of the SQL query based on the search criteria.
     *
     * @param CommonQueryBuilder $queryBuilder
     * @param LogSearchRequest $request
     * @param int|null $appId
     */
    abstract public function findLogFilter(
        CommonQueryBuilder $queryBuilder,
        LogSearchRequest $request,
        ?int $appId = null
    ): void;

    /**
     * Builds the SELECT SQL query.
     *
     * @param CommonQueryBuilder $queryBuilder
     * @param string $order
     * @param int $offset
     * @param int $limit
     */
    abstract public function findLogSelect(
        CommonQueryBuilder $queryBuilder,
        string $order,
        int $offset,
        int $limit
    ): void;

    /**
     * {@inheritDoc}
     */
    public function findLogByAppIdAndLogId(int $appId, int $logId): SqlLog|null
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->where('l.id = :logId')
            ->andWhere('l.appId = :appId')
            ->setParameter('logId', $logId)
            ->setParameter('appId', $appId);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritDoc}
     */
    public function shouldSendNotification(int $appId, array $alertConfig): ?SqlLog
    {
        // Calculate the duration to use in the query
        $interval = new DateInterval("PT{$alertConfig['duration']}M");
        $dateTime = new DateTime();
        $duration = (new DateTimeHelper())
            ->dateTimeToStr($dateTime->sub($interval));

        // Create the query builder and set the initial conditions
        $subQuery = $this->createQueryBuilder('l')
            ->select(
                "COUNT(l.id) as size",
                "MAX(l.id) as id"
            )
            ->where('l.appId = :appId AND l.dateTime >= :duration')
            ->setParameter('appId', $appId)
            ->setParameter('duration', $duration);

        // Loop through the alert configuration and add additional conditions to the query
        self::findAppLogByAlertConfigFilter($subQuery, $alertConfig);

        $subQueryResult = $subQuery->getQuery()->getSingleResult();
        $query = $this->createQueryBuilder('l');
        $query
            ->where($query->expr()->eq('l.id', ':maxId'))
            ->setParameter('maxId', $subQueryResult['id'])
            ->andWhere($query->expr()->gte($subQueryResult['size'], $alertConfig['manyFailures']));

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * Apply filters to a query of the findAppLogByAlertConfig based on an array of alert configurations.
     *
     * @param QueryBuilder $queryBuilder
     * @param array $alertConfig
     *
     * @return void
     */
    private function findAppLogByAlertConfigFilter(QueryBuilder $queryBuilder, array $alertConfig): void
    {
        $filters = ['message', 'stackTrace', 'browser', 'os', 'device', 'additional'];

        foreach ($alertConfig as $key => $value) {
            if (!empty($value)) {
                if ("level" === $key) {
                    $levelValues = [];

                    foreach ($value as $val) {
                        $levelValues[] = strtolower($val['level']);
                    }

                    $queryBuilder->andWhere(
                        $queryBuilder->expr()->in(
                            'LOWER(l.level)',
                            $levelValues
                        )
                    );
                }

                if (in_array($key, $filters)) {
                    $filter = strtolower($value);
                    $queryBuilder->andWhere(
                        $queryBuilder->expr()->like(
                            "LOWER(l.{$key})",
                            $queryBuilder->expr()
                                ->literal("%$filter%")
                        )
                    );
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws NonUniqueResultException
     */
    public function findAppIdByLogIdAndAssignee(int $logId, int $assignee): array
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->select('l.appId', "CASE WHEN l.assignee = {$assignee} THEN 1 ELSE 0 END AS exists")
            ->where('l.id = :logId')
            ->setParameter('logId', $logId);

        return $queryBuilder->getQuery()->getOneOrNullResult() ?? [];
    }

    /**
     * {@inheritDoc}
     */
    public function save(SqlLog $log): void
    {
        $this->getEntityManager()->persist($log);
        $this->getEntityManager()->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function deleteByAppId(int $appId): void
    {
        $query = $this->getEntityManager()
            ->createQuery(
                "delete from App\Entity\SqlLog l where l.appId = {$appId}"
            );
        $query->execute();
    }

    /**
     * Refactors the chart log result array to the desired format.
     *
     * @param array $chartLog
     *
     * @return array
     */
    protected function refactorChartLogResult(array $chartLog): array
    {
        $refactorResult = [];

        foreach ($chartLog as $chart) {
            $log = [];
            foreach ($chart as $key => $value) {
                if ($key === 'log_date') {
                    $log[$key] = $value;
                } else {
                    $log['logCount'][$key] = $value;
                }
            }

            $refactorResult[] = $log;
        }

        return $refactorResult;
    }
}