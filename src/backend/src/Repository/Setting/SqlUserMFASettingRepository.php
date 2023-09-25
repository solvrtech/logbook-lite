<?php

namespace App\Repository\Setting;

use App\Entity\UserMFASetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

abstract class SqlUserMFASettingRepository
    extends ServiceEntityRepository
    implements UserMFASettingRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct($registry, UserMFASetting::class);
    }

    /**
     * {@inheritDoc}
     */
    public function save(UserMFASetting $userMFASetting): void
    {
        $this->getEntityManager()->persist($userMFASetting);
        $this->getEntityManager()->flush();
    }
}