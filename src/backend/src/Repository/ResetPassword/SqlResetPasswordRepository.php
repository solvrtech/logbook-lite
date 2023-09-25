<?php

namespace App\Repository\ResetPassword;

use App\Entity\ResetPassword;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

abstract class SqlResetPasswordRepository
    extends ServiceEntityRepository
    implements ResetPasswordRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, ResetPassword::class);
    }

    /**
     * {@inheritDoc}
     *
     * @throws NonUniqueResultException
     */
    public function findResetPasswordByToken(string $token): ResetPassword|null
    {
        $queryBuilder = $this->createQueryBuilder('rp')
            ->where('rp.token = :token')
            ->setParameter('token', $token);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritDoc}
     *
     * @throws NonUniqueResultException
     */
    public function findResetPasswordByEmail(string $email): ResetPassword|null
    {
        $queryBuilder = $this->createQueryBuilder('rp')
            ->where('rp.email = :email')
            ->setParameter('email', $email);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritDoc}
     */
    public function save(ResetPassword $resetPassword): void
    {
        $this->getEntityManager()->persist($resetPassword);
        $this->getEntityManager()->flush();
    }

    /**
     * {@inhetiDoc}
     */
    public function deleteExpiredResetPasswords(DateTime $expiryDate): void
    {
        $queryBuilder = $this->createQueryBuilder('rp')
            ->where('rp.expiredAt < :expiryDate')
            ->setParameter('expiryDate', $expiryDate)
            ->delete();

        $queryBuilder->getQuery()->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(ResetPassword $resetPassword): void
    {
        $this->getEntityManager()->remove($resetPassword);
        $this->getEntityManager()->flush();
    }
}