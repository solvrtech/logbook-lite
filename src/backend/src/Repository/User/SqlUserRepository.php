<?php

namespace App\Repository\User;

use App\Common\Config\CommonConfig;
use App\Common\Config\TeamConfig;
use App\Common\Config\UserConfig;
use App\Entity\User;
use App\Model\Pagination;
use App\Model\Request\SearchRequest;
use App\Service\Search\PaginatorInterface;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

abstract class SqlUserRepository
    extends ServiceEntityRepository
    implements UserRepositoryInterface
{
    public PaginatorInterface $pagination;

    public function __construct(
        PaginatorInterface $pagination,
        ManagerRegistry $registry
    ) {
        $this->pagination = $pagination;

        parent::__construct($registry, User::class);
    }

    /**
     * {@inheritDoc}
     */
    public function findUser(SearchRequest $searchRequest): Pagination
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->where('u.deletedAt IS NULL');

        if ($searchRequest->getSearch()) {
            $queryBuilder
                ->andWhere(
                    "(LOWER(u.name) LIKE '%{$searchRequest->getSearch()}%' OR LOWER(u.email) LIKE '%{$searchRequest->getSearch()}%')"
                );
        }

        $totalItems = $queryBuilder
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
        $queryBuilder->resetDQLPart('select');
        $queryResult = $queryBuilder
            ->select('u')
            ->orderBy('u.id', CommonConfig::ORDER)
            ->setFirstResult($searchRequest->offset())
            ->setMaxResults($searchRequest->getSize())
            ->getQuery()
            ->getResult();

        return $this->pagination->getResult(
            $queryResult,
            $totalItems,
            $searchRequest->getPage(),
            $searchRequest->getSize()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function findUserByAppId(int $appId): array
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->join('u.userTeam', 'ut')
            ->join('ut.team', 't')
            ->join('t.teamApp', 'ta')
            ->join('ta.app', 'a')
            ->where('a.id = :appId')
            ->andWhere('u.deletedAt IS NULL')
            ->setParameter('appId', $appId);

        return $queryBuilder
            ->select('u.id', 'u.name', 'u.email', 'u.role')
            ->groupBy('u.id')
            ->orderBy('u.id', CommonConfig::ORDER)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * {@inheritDoc}
     *
     * @throws NonUniqueResultException
     */
    public function findUserByEmail(string $email): User|null
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->where('u.deletedAt is null')
            ->andWhere('u.email = :email')
            ->setParameter('email', $email);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritDoc}
     *
     * @throws NonUniqueResultException
     */
    public function findUserById(int|string $id): User|null
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->where('u.deletedAt is null')
            ->andWhere('u.id = :id')
            ->setParameter('id', $id);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritDoc}
     */
    public function findUserAssignedToApp(int $appId, string $role): array
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.deletedAt IS NULL')
            ->andWhere('u.role = :userRole')
            ->setParameter('userRole', UserConfig::ROLE_ADMIN);
        $subQb = $this->getEntityManager()->createQueryBuilder()
            ->select('usub.id')
            ->from(User::class, 'usub')
            ->join('usub.userTeam', 'ut')
            ->join('ut.team', 't')
            ->join('t.teamApp', 'ta')
            ->join('ta.app', 'a')
            ->where('usub.deletedAt IS NULL')
            ->andWhere('a.id = :appId');

        if (TeamConfig::TEAM_MANAGER === $role || TeamConfig::TEAM_STANDARD === $role) {
            $subQb->andWhere('ut.role = :role');
            $qb->setParameter('role', $role);
        }

        $qb->orWhere($qb->expr()->in('u.id', $subQb->getDQL()))
            ->setParameter('appId', $appId);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritDoc}
     *
     * @throws NonUniqueResultException
     */
    public function findUserAssignedToAppByUserId(int $appId, int $userId): User|null
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->join('u.userTeam', 'ut')
            ->join('ut.team', 't')
            ->join('t.teamApp', 'ta')
            ->join('ta.app', 'a')
            ->where('a.id = :appId')
            ->andWhere('u.id = :userId')
            ->andWhere('u.deletedAt IS NULL')
            ->setParameter('appId', $appId)
            ->setParameter('userId', $userId);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritDoc}
     */
    public function findUserTeamRole(string $email, ?int $appId = null): array
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->select('ut.role', 'a.id as app')
            ->join('u.userTeam', 'ut')
            ->join('ut.team', 't')
            ->join('t.teamApp', 'ta')
            ->join('ta.app', 'a')
            ->andWhere('u.email = :email')
            ->andWhere('u.deletedAt IS NULL')
            ->setParameter('email', $email);

        if (null !== $appId) {
            $queryBuilder
                ->andWhere('a.id = :appId')
                ->setParameter('appId', $appId);
        }

        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * {@inheritDoc}
     */
    public function findAllStandardUser(): array
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->select('u.id', 'u.name', 'u.email', 'u.role')
            ->where('u.deletedAt IS NULL')
            ->andWhere('u.role = :role')
            ->setParameter('role', UserConfig::ROLE_STANDARD)
            ->orderBy('u.name');

        return $queryBuilder
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * {@inheritDoc}
     */
    public function isUserHasTeam(User $user): array
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->leftJoin('u.userTeam', 'ut')
            ->leftJoin('ut.team', 't')
            ->leftJoin('t.teamApp', 'ta')
            ->leftJoin('ta.app', 'a')
            ->where('u.id = :userId')
            ->setParameter('userId', $user->getId());

        return $queryBuilder
            ->select(
                'CASE WHEN COUNT(DISTINCT t.id) > 0 THEN true ELSE false END as team',
                'CASE WHEN COUNT(DISTINCT a.id) > 0 THEN true ELSE false END as app'
            )
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * {@inheritDoc}
     */
    public function isTeamManager(User $user): array
    {
        $role = TeamConfig::TEAM_MANAGER;

        $subDQL = $this->createQueryBuilder('uSub')
            ->select('tSub.id')
            ->join('uSub.userTeam', 'utSub')
            ->join('utSub.team', 'tSub')
            ->where('utSub.role = :teamRole')
            ->groupBy('tSub.id')
            ->having('COUNT(uSub.id) = 1')
            ->getDQL();

        $subQB = $this->getEntityManager()->createQuery($subDQL);
        $subQB->setParameter('teamRole', $role);

        $QB = $this->createQueryBuilder('u')
            ->select('DISTINCT t.name')
            ->join('u.userTeam', 'ut')
            ->join('ut.team', 't')
            ->join('t.teamApp', 'ta')
            ->join('ta.app', 'a')
            ->where('t.id IN (:teamManager)')
            ->andWhere('u.id = :userId')
            ->andWhere('u.deletedAt IS NULL')
            ->andWhere('ut.role = :teamRole')
            ->setParameter('teamManager', $subQB->getResult())
            ->setParameter('userId', $user->getId())
            ->setParameter('teamRole', $role);

        return $QB->getQuery()->getArrayResult();
    }

    /**
     * {@inheritDoc}
     */
    public function findTotalAdmin(): int
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->where('u.deletedAt IS NULL')
            ->andWhere('u.role = :role')
            ->setParameter('role', UserConfig::ROLE_ADMIN)
            ->select('COUNT(u.id)');

        return $queryBuilder
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * {@inheritDoc}
     */
    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function softDelete(User $user): void
    {
        $user->setDeletedAt(new DateTime());

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * {@inheritDoc}
     *
     * @throws NonUniqueResultException
     */
    public function uniqueEmail(string $email, ?int $id = null): bool
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->select('u.id')
            ->where('u.deletedAt is null')
            ->andWhere('u.email = :email')
            ->setParameter('email', $email);

        if ($id) {
            $queryBuilder->andWhere('u.id != :id')
                ->setParameter('id', $id);
        }

        return $queryBuilder->getQuery()->getOneOrNullResult() !== null ?? false;
    }
}