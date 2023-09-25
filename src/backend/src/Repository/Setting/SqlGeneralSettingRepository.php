<?php

namespace App\Repository\Setting;

use App\Entity\GeneralSetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

abstract class SqlGeneralSettingRepository
    extends ServiceEntityRepository
    implements GeneralSettingRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct($registry, GeneralSetting::class);
    }

    /**
     * {@inheritDoc}
     *
     * @throws NonUniqueResultException
     */
    public function findSetting(): GeneralSetting|null
    {
        $queryBuilder = $this->createQueryBuilder('gs')
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritDoc}
     */
    public function save(GeneralSetting $setting): void
    {
        $this->getEntityManager()->persist($setting);
        $this->getEntityManager()->flush();
    }
}