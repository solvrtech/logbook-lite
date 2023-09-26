<?php

namespace App\Repository\App;

use App\Common\CommonHelper;
use App\Common\Config\CommonConfig;
use App\Common\Config\UserConfig;
use App\Common\QueryBuilder;
use App\Entity\App;
use App\Entity\User;
use App\Model\Pagination;
use App\Model\Request\SearchRequest;
use App\Model\Response\AppSearchResponse;
use App\Service\Search\SearchResultServiceInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

abstract class SqlAppRepository
    extends ServiceEntityRepository
    implements AppRepositoryInterface
{
    public SearchResultServiceInterface $resultService;
    public Connection $connection;

    public function __construct(
        SearchResultServiceInterface $resultService,
        ManagerRegistry $registry,
        Connection $connection
    ) {
        $this->resultService = $resultService;
        $this->connection = $connection;

        parent::__construct($registry, App::class);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllApps(): array
    {
        $queryBuilder = $this->createQueryBuilder('a');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritDoc}
     */
    public function getNameAndIdAllApps(User $user): array
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->select('a.id', 'a.name');

        if (UserConfig::ROLE_ADMIN !== $user->getRole()) {
            $queryBuilder
                ->join('a.teamApp', 'ta')
                ->join('ta.team', 't')
                ->join('t.userTeam', 'ut')
                ->join('ut.user', 'u')
                ->where('u.id = :userId')
                ->setParameter('userId', $user->getId());
        }

        $queryBuilder->groupBy('a.id')
            ->orderBy('a.id', CommonConfig::ORDER);

        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * {@inheritDoc}
     *
     * @throws NonUniqueResultException
     */
    public function findAppById(int $id, User $user = null): App|null
    {
        $queryBuilder = $this->createQueryBuilder('a');

        if (null !== $user) {
            if (UserConfig::ROLE_ADMIN !== $user->getRole()) {
                $queryBuilder
                    ->join('a.teamApp', 'ta')
                    ->join('ta.team', 't')
                    ->join('t.userTeam', 'ut')
                    ->join('ut.user', 'u')
                    ->where('u.id = :userId')
                    ->setParameter('userId', $user->getId());
            }
        }

        $queryBuilder->andWhere('a.id = :id')
            ->setParameter('id', $id)
            ->groupBy('a.id');

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritDoc}
     *
     * @throws NonUniqueResultException
     */
    public function findAppByKey(string $apiKey): App|null
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->where('a.apiKey = :apiKey')
            ->setParameter('apiKey', $apiKey);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function findApp(User $user, SearchRequest $searchRequest): Pagination
    {
        $qb = (new QueryBuilder())
            ->table('app a')
            ->leftJoin('app_logo al ON a.id = al.app_id');
        $this->findLogJoinQuery($qb, $user);

        if ($searchRequest->getSearch()) {
            $qb->where(
                "(LOWER(a.name) LIKE '%{$searchRequest->getSearch()}%' OR LOWER(a.description) LIKE '%{$searchRequest->getSearch()}%')"
            );
        }

        $totalItems = $this->connection
            ->executeQuery(
                $qb
                    ->select("SELECT count(DISTINCT a.id)")
                    ->build()
            )
            ->fetchOne();
        $qb
            ->select(
                'SELECT DISTINCT a.id, a.name, a.description, a.type, al.public_path AS app_logo, 1 AS is_team_manager'
            )
            ->order('a.id '.CommonConfig::ORDER);

        if (UserConfig::ROLE_ADMIN !== $user->getRole()) {
            $qb
                ->select(
                    'SELECT DISTINCT a.id, a.name, a.description, a.type, al.public_path AS app_logo, CASE WHEN tm.id IS NOT NULL THEN TRUE ELSE FALSE END AS is_team_manager'
                );
        }

        return $this->resultService->paginationResult(
            (new CommonHelper())->arraySnakeToCamelCase(
                $this->connection->fetchAllAssociative($qb->build())
            ),
            $totalItems,
            $searchRequest->getPage(),
            $searchRequest->getSize(),
            AppSearchResponse::class
        );
    }

    /**
     * Builds the JOIN query of the SQL query
     *
     * @param QueryBuilder $queryBuilder
     * @param User $user
     */
    abstract public function findLogJoinQuery(QueryBuilder $queryBuilder, User $user): void;

    /**
     * {@inheritDoc}
     *
     * @throws NonUniqueResultException
     */
    public function findAppByIdAndName(int $appId, string $name, User $user): App|null
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->distinct('a.id')
            ->andWhere('a.id = :appId AND  LOWER(a.name) = :name')
            ->setParameter('appId', $appId)
            ->setParameter('name', $name);

        if (UserConfig::ROLE_ADMIN !== $user->getRole()) {
            $queryBuilder
                ->join('a.teamApp', 'ta')
                ->join('ta.team', 't')
                ->join('t.userTeam', 'ut')
                ->join('ut.user', 'u')
                ->andWhere('u.id = :userId')
                ->setParameter('userId', $user->getId());
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritDoc}
     */
    public function save(App $app): void
    {
        $this->getEntityManager()->persist($app);
        $this->getEntityManager()->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(App $app): void
    {
        $this->getEntityManager()->remove($app);
        $this->getEntityManager()->flush();
    }
}