<?php

namespace App\Repository\User;

use App\Entity\Invitation;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

abstract class SqlInvitationRepository
    extends ServiceEntityRepository
    implements InvitationRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, Invitation::class);
    }

    /**
     * {@inheritDoc}
     *
     * @throws NonUniqueResultException
     */
    public function findInvitationByEmail(string $email): Invitation|null
    {
        $queryBuilder = $this->createQueryBuilder('i')
            ->where('i.email = :email')
            ->setParameter('email', $email);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritDoc}
     *
     * @throws NonUniqueResultException
     */
    public function findInvitationByToken(string $token, DateTime $expiryDate): Invitation|null
    {
        $queryBuilder = $this->createQueryBuilder('i')
            ->where('i.token = :token')
            ->andWhere('i.expiredAt > :expiryDate')
            ->setParameter('token', $token)
            ->setParameter('expiryDate', $expiryDate);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritDoc}
     */
    public function save(Invitation $invitation): void
    {
        $this->getEntityManager()->persist($invitation);
        $this->getEntityManager()->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function deleteExpiredInvitations(DateTime $expiryDate): void
    {
        $queryBuilder = $this->createQueryBuilder('i')
            ->where('i.expiredAt < :expiryDate')
            ->setParameter('expiryDate', $expiryDate)
            ->delete();

        $queryBuilder->getQuery()->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(Invitation $invitation): void
    {
        $this->getEntityManager()->remove($invitation);
        $this->getEntityManager()->flush();
    }
}