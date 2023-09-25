<?php

namespace App\Repository\Health;

use App\Common\CommonHelper;
use App\Common\Config\CommonConfig;
use App\Common\DateTimeHelper;
use App\Common\QueryBuilder;
use App\Common\QueryBuilder as CommonQueryBuilder;
use App\Entity\HealthStatus;
use App\Entity\User;
use App\Model\Pagination;
use App\Model\Request\HealthStatusSearchRequest;
use App\Service\Search\SearchResultServiceInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

abstract class SqlHealthStatusRepository
    extends ServiceEntityRepository
    implements HealthStatusRepositoryInterface
{
    public Connection $connection;
    public DateTimeHelper $format;
    public SearchResultServiceInterface $resultService;

    public function __construct(
        Connection $connection,
        DateTimeHelper $format,
        SearchResultServiceInterface $resultService,
        ManagerRegistry $registry
    ) {
        $this->connection = $connection;
        $this->format = $format;
        $this->resultService = $resultService;

        parent::__construct($registry, HealthStatus::class);
    }

    /**
     * {@inheritDoc}
     */
    public function findHealthStatus(
        User $user,
        HealthStatusSearchRequest $searchRequest,
        ?int $appId = null
    ): Pagination {
        $queryBuilder = (new QueryBuilder())->table("health_status hs");
        $this->findHealthStatusJoin($queryBuilder, $user);

        if ($appId) {
            $queryBuilder->where("a.id = {$appId}");
        }

        self::findLogFilter($queryBuilder, $searchRequest);
        $totalItems = $this->connection
            ->executeQuery(
                $queryBuilder
                    ->select("SELECT COUNT(DISTINCT hs.id)")
                    ->build()
            )
            ->fetchOne();
        $order = CommonConfig::ORDER;
        $this->findHealthStatusSelect(
            $queryBuilder,
            $order,
            $searchRequest->offset(),
            $searchRequest->getSize()
        );

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
     * Builds the JOIN SQL query.
     *
     * @param CommonQueryBuilder $queryBuilder
     * @param User $user
     */
    abstract public function findHealthStatusJoin(CommonQueryBuilder $queryBuilder, User $user): void;

    /**
     * Builds the WHERE clause of the SQL query based on the search criteria.
     *
     * @param QueryBuilder $queryBuilder
     * @param HealthStatusSearchRequest $request
     */
    private function findLogFilter(QueryBuilder $queryBuilder, HealthStatusSearchRequest $request): void
    {
        if ($request->getStatus()) {
            $queryBuilder
                ->where("hs.status = '{$request->getStatus()}'");
        }

        if (
            $request->getStartDateTime() &&
            $request->getEndDateTime()
        ) {
            $queryBuilder
                ->where("hs.created_at >= '{$request->getStartDateTime()}'")
                ->Where("hs.created_at <= '{$request->getEndDateTime()}'");
        }
    }

    /**
     * Builds the SELECT SQL query.
     *
     * @param CommonQueryBuilder $queryBuilder
     * @param string $order
     * @param int $offset
     * @param int $limit
     */
    abstract public function findHealthStatusSelect(
        CommonQueryBuilder $queryBuilder,
        string $order,
        int $offset,
        int $limit
    ): void;

    /**
     * {@inheritDoc}
     */
    public function findAppHealthStatusById(int $appId, int $healthStatusId): HealthStatus|null
    {
        $queryBuilder = $this->createQueryBuilder('hs')
            ->join('hs.app', 'a')
            ->leftJoin('hs.healthCheck', 'hc')
            ->where('a.id = :appId')
            ->andWhere('hs.id = :healthStatusId')
            ->setParameter('appId', $appId)
            ->setParameter('healthStatusId', $healthStatusId);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inhertiDoc}
     */
    public function findAppIdByHealthStatusId(int $healthStatusId): int|null
    {
        $queryBuilder = $this->createQueryBuilder('hs')
            ->join('hs.app', 'a')
            ->select('a.id')
            ->where('hs.id = :id')
            ->setParameter('id', $healthStatusId);

        try {
            return $queryBuilder->getQuery()->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException $exception) {
            return null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function save(HealthStatus $healthStatus): void
    {
        $this->getEntityManager()->persist($healthStatus);
        $this->getEntityManager()->flush();
    }
}
