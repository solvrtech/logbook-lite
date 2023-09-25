<?php

namespace App\Repository\Team;

use App\Common\Config\CommonConfig;
use App\Common\Config\TeamConfig;
use App\Common\Config\UserConfig;
use App\Entity\Team;
use App\Entity\User;
use App\Model\Pagination;
use App\Model\Request\SearchRequest;
use App\Model\Response\TeamSearchResponse;
use App\Service\Search\PaginatorInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

abstract class SqlTeamRepository
    extends ServiceEntityRepository
    implements TeamRepositoryInterface
{
    public PaginatorInterface $pagination;

    public function __construct(
        PaginatorInterface $pagination,
        ManagerRegistry $registry
    ) {
        $this->pagination = $pagination;

        parent::__construct($registry, Team::class);
    }

    /**
     * {@inheritDoc}
     */
    public function findTeam(User $user, SearchRequest $searchRequest): Pagination
    {
        $query = $this->createQueryBuilder('t')
            ->join('t.userTeam', 'ut')
            ->join('ut.user', 'u');

        if ($searchRequest->getSearch()) {
            $query
                ->where("LOWER(t.name) LIKE :search")
                ->setParameter('search', "%{$searchRequest->getSearch()}%");
        }

        if (UserConfig::ROLE_ADMIN !== $user->getRole()) {
            $query
                ->andWhere('u.id = :userId')
                ->setParameter('userId', $user->getId());
        }

        $totalItems = $this->getTotalItems($query);
        $queryResult = $this->getQueryResult($query, $user, $searchRequest);

        return $this->pagination->getResult(
            $queryResult,
            $totalItems,
            $searchRequest->getPage(),
            $searchRequest->getSize(),
            TeamSearchResponse::class
        );
    }

    /**
     * Retrieves the total items of the team based on the provided query.
     *
     * @param QueryBuilder $query
     *
     * @return int
     *
     * @throws NonUniqueResultException
     */
    private function getTotalItems(QueryBuilder $query): int
    {
        return $query
            ->select('COUNT(DISTINCT t.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Executes the main query of find team and returns the result.
     *
     * @param QueryBuilder $query
     * @param User $user
     * @param SearchRequest $searchRequest
     *
     * @return array
     */
    private function getQueryResult(QueryBuilder $query, User $user, SearchRequest $searchRequest): array
    {
        $query->select(
            't.id',
            't.name',
            '(SELECT COUNT(ut1.id) FROM App\Entity\UserTeam ut1 INNER JOIN ut1.team t1 WHERE t1.id = t.id) as member',
            '(SELECT COUNT(ta1.id) FROM App\Entity\TeamApp ta1 LEFT JOIN ta1.team t2 WHERE t2.id = t.id) as totalApp',
        )
            ->orderBy('t.id', CommonConfig::ORDER)
            ->setFirstResult($searchRequest->offset())
            ->setMaxResults($searchRequest->getSize())
            ->groupBy('t.id', 't.name');

        if (UserConfig::ROLE_ADMIN !== $user->getRole()) {
            $teamManager = TeamConfig::TEAM_MANAGER;
            $query
                ->addSelect("CASE WHEN ut.role = '{$teamManager}' THEN true ELSE false END as isTeamManager")
                ->addGroupBy('isTeamManager');
        } else {
            $query->addSelect('1 as isTeamManager');
        }

        return $query->getQuery()->getArrayResult();
    }

    /**
     * {@inheritDoc}
     */
    public function getAll(): array
    {
        $queryBuilder = $this->createQueryBuilder('t')
            ->join('t.userTeam', 'ut')
            ->select('t.id', 't.name', 'count(ut.id) as member')
            ->orderBy('t.name')
            ->groupBy('t.id', 't.name');

        return $queryBuilder
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * {@inheritDoc}
     *
     * @throws NonUniqueResultException
     */
    public function findTeamById(User $user, int $id): Team|null
    {
        $queryBuilder = $this->createQueryBuilder('t')
            ->join('t.userTeam', 'ut')
            ->join('ut.user', 'u')
            ->where('t.id = :id')
            ->setParameter('id', $id);

        if (UserConfig::ROLE_ADMIN !== $user->getRole()) {
            $queryBuilder
                ->andWhere('u.id = :userId')
                ->setParameter('userId', $user->getId());
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inhertiDoc}
     */
    public function getTeamRoleOfUser(int $userId, int $teamId): string
    {
        $queryBuilder = $this->createQueryBuilder('t')
            ->select('ut.role')
            ->join('t.userTeam', 'ut')
            ->join('ut.user', 'u')
            ->where('t.id = :id')
            ->andWhere('u.id = :userId')
            ->setParameter('id', $teamId)
            ->setParameter('userId', $userId);

        return $queryBuilder
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * {@inheritDoc}
     */
    public function save(Team $team): void
    {
        $this->getEntityManager()->persist($team);
        $this->getEntityManager()->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(Team $team): void
    {
        $this->getEntityManager()->remove($team);
        $this->getEntityManager()->flush();
    }
}