<?php

namespace App\Repository\Setting;

use App\Entity\HealthStatusSetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

abstract class SqlHealthStatusSettingRepository
    extends ServiceEntityRepository
    implements HealthStatusSettingRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, HealthStatusSetting::class);
    }

    /**
     * {@inheritDoc}
     *
     * @throws NonUniqueResultException
     */
    public function findAppHealthStatus(int $appId): HealthStatusSetting|null
    {
        $queryBuilder = $this->createQueryBuilder('aps')
            ->join('aps.app', 'a')
            ->where('a.id = :appId')
            ->setParameter('appId', $appId);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritDoc}
     */
    public function getAllHealthSettings(): array
    {
        $queryBuilder = $this->createQueryBuilder('hss')
            ->join('hss.app', 'a')
            ->andWhere('hss.isEnabled = TRUE')
            ->select('hss.url', 'hss.period', 'a.id', 'a.apiKey', 'a.type');

        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * {@inheritDoc}
     */
    public function findPeriodHealthCheckByAppId(int $appId): int
    {
        $queryBuilder = $this->createQueryBuilder('aps')
            ->join('aps.app', 'a')
            ->where('a.id = :appId')
            ->setParameter('appId', $appId);

        try {
            $period = $queryBuilder
                ->select('aps.period')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
            $period = 15;
        }

        return $period;
    }

    /**
     * {@inheritDoc}
     */
    public function save(HealthStatusSetting $healthStatusSetting): void
    {
        $this->getEntityManager()->persist($healthStatusSetting);
        $this->getEntityManager()->flush();
    }
}
