<?php

namespace App\Repository\Log;

use App\Entity\LogComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

abstract class SqlLogCommentRepository
    extends ServiceEntityRepository
    implements LogCommentRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, LogComment::class);
    }

    /**
     * {@inheritDoc}
     *
     * @throws NonUniqueResultException
     */
    public function findLogCommentById(int $commentId, int $userId): LogComment|null
    {
        $queryBuilder = $this->createQueryBuilder('lc')
            ->join('lc.user', 'u')
            ->where('lc.id = :commentId')
            ->andWhere('u.id = :userId')
            ->setParameter('commentId', $commentId)
            ->setParameter('userId', $userId);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritDoc}
     */
    public function save(LogComment $logComment): void
    {
        $this->getEntityManager()->persist($logComment);
        $this->getEntityManager()->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(LogComment $logComment): void
    {
        $this->getEntityManager()->remove($logComment);
        $this->getEntityManager()->flush();
    }
}