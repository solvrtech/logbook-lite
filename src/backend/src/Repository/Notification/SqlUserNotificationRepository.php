<?php

namespace App\Repository\Notification;

use App\Entity\User;
use App\Entity\UserNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

abstract class SqlUserNotificationRepository
    extends ServiceEntityRepository
    implements UserNotificationRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, UserNotification::class);
    }

    /**
     * {@inheritDoc}
     */
    public function findUserNotificationId(User $user, int $notificationId): UserNotification|null
    {
        $queryBuilder = $this->createQueryBuilder('un')
            ->join('un.user', 'u')
            ->join('un.notification', 'n')
            ->where('u.id = :userId')
            ->andWhere('n.id = :notificationId')
            ->setParameter('userId', $user->getId())
            ->setParameter('notificationId', $notificationId);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inhertiDoc}
     */
    public function bulkDelete(User $user, array $ids): void
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->delete('App\Entity\UserNotification', 'un')
            ->where('un.user = :userId')
            ->andWhere($queryBuilder->expr()->in('un.notification', ':ids'))
            ->setParameter('userId', $user->getId())
            ->setParameter('ids', $ids);

        $queryBuilder->getQuery()->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(UserNotification $userNotification): void
    {
        $this->getEntityManager()->remove($userNotification);
        $this->getEntityManager()->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function deleteAll(User $user): void
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->delete('App\Entity\UserNotification', 'un')
            ->where('un.user = :userId')
            ->setParameter('userId', $user->getId());

        $queryBuilder->getQuery()->execute();
    }
}