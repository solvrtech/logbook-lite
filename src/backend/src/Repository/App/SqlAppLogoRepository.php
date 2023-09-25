<?php

namespace App\Repository\App;

use App\Entity\AppLogo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

class SqlAppLogoRepository extends ServiceEntityRepository implements AppLogoRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, AppLogo::class);
    }

    /**
     * {@inheritDoc}
     */
    public function isCombinationUnique(string $bgColor, string $initials): bool
    {
        $queryBuilder = $this->createQueryBuilder('al')
            ->where('al.bgColor = :bgColor')
            ->andWhere('al.initials = :initials')
            ->setParameter('bgColor', $bgColor)
            ->setParameter('initials', $initials);

        try {
            $appLogo = $queryBuilder
                ->select('al.id')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
            $appLogo = false;
        }

        return $appLogo ?: false;
    }
}