<?php

namespace App\Repository\Alert;

use App\Common\Config\CommonConfig;
use App\Common\Config\UserConfig;
use App\Entity\AlertSetting;
use App\Entity\User;
use App\Model\Pagination;
use App\Model\Request\SearchRequest;
use App\Service\Search\PaginatorInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

abstract class SqlAlertSettingRepository
    extends ServiceEntityRepository
    implements AlertSettingRepositoryInterface
{
    public PaginatorInterface $pagination;

    public function __construct(
        PaginatorInterface $pagination,
        ManagerRegistry $registry
    ) {
        $this->pagination = $pagination;

        parent::__construct($registry, AlertSetting::class);
    }

    /**
     * {@inheritDoc}
     */
    public function findAlert(User $user, int $appId, SearchRequest $searchRequest): Pagination
    {
        $queryBuilder = $this->createQueryBuilder('al')
            ->join('al.app', 'a')
            ->andWhere('a.id = :appId')
            ->setParameter('appId', $appId);

        if (UserConfig::ROLE_ADMIN !== $user->getRole()) {
            $queryBuilder
                ->join('a.teamApp', 'ta')
                ->join('ta.team', 't')
                ->join('t.userTeam', 'ut')
                ->join('ut.user', 'u')
                ->andWhere('u.id = :userId')
                ->setParameter('userId', $user->getId());
        }

        // search
        if ($searchRequest->getSearch()) {
            $queryBuilder->andWhere(
                "LOWER(al.name) LIKE '%{$searchRequest->getSearch()}%'"
            );
        }

        try {
            $totalItems = $queryBuilder
                ->select('COUNT(DISTINCT al.id)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (Exception $exception) {
            $totalItems = 0;
        }

        $queryResult = $queryBuilder
            ->select('al.id', 'al.name', 'al.active', 'al.source', 'al.lastNotified')
            ->orderBy('al.id', CommonConfig::ORDER)
            ->groupBy('al.id')
            ->setFirstResult($searchRequest->offset())
            ->setMaxResults($searchRequest->getSize())
            ->getQuery()
            ->getArrayResult();

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
    public function findAppAlert(int $appId, string $source): array
    {
        $queryBuilder = $this->createQueryBuilder('al')
            ->select(
                'al.id',
                'al.source',
                'al.config',
                'al.notifyTo',
                'al.restrictNotify',
                'al.notifyLimit',
            )
            ->join('al.app', 'a')
            ->where('a.id = :appId')
            ->andWhere('al.active = true')
            ->andWhere('al.source = :source')
            ->setParameter('appId', $appId)
            ->setParameter('source', $source)
            ->orderBy('a.id');

        $result = $queryBuilder->getQuery()->getArrayResult();

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function findAppAlertById(int $appId, int $alertId, ?User $user = null): AlertSetting|null
    {
        $queryBuilder = $this->createQueryBuilder('al')
            ->join('al.app', 'a')
            ->where('al.id = :id')
            ->andWhere('a.id = :appId')
            ->setParameter('appId', $appId)
            ->setParameter('id', $alertId)
            ->groupBy('al.id');

        if ($user) {
            if (UserConfig::ROLE_ADMIN !== $user->getRole()) {
                $queryBuilder
                    ->join('a.teamApp', 'ta')
                    ->join('ta.team', 't')
                    ->join('t.userTeam', 'ut')
                    ->join('ut.user', 'u')
                    ->andWhere('u.id = :userId')
                    ->setParameter('userId', $user->getId());
            }
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritDoc}
     */
    public function getAll(): array
    {
        $queryBuilder = $this->createQueryBuilder('al')
            ->select(
                'al.id',
                'a.id AS appId',
                'a.name AS appName',
                'al.source',
                'al.config',
                'al.notifyTo',
                'al.restrictNotify',
                'al.notifyLimit',
            )
            ->join('al.app', 'a')
            ->where('al.active = true')
            ->orderBy('a.id');

        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * {@inheritDoc}
     */
    public function save(AlertSetting $alert): void
    {
        $this->getEntityManager()->persist($alert);
        $this->getEntityManager()->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(AlertSetting $alert): void
    {
        $this->getEntityManager()->remove($alert);
        $this->getEntityManager()->flush();
    }
}