<?php

namespace App\Repository\Setting;

use App\Entity\SecuritySetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

abstract class SqlSecuritySettingRepository
    extends ServiceEntityRepository
    implements SecuritySettingRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct($registry, SecuritySetting::class);
    }

    /**
     * @inheritDoc
     *
     * @throws NonUniqueResultException
     */
    public function findSetting(): SecuritySetting|null
    {
        $queryBuilder = $this->createQueryBuilder('ss')
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    public function save(SecuritySetting $setting): void
    {
        $this->getEntityManager()->persist($setting);
        $this->getEntityManager()->flush();
    }
}