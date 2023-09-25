<?php

namespace App\Repository\Notification;

use App\Common\Config\CommonConfig;
use App\Entity\Notification;
use App\Entity\User;
use App\Model\Pagination;
use App\Model\Request\SearchRequest;
use App\Service\Search\PaginatorInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

abstract class SqlNotificationRepository
    extends ServiceEntityRepository
    implements NotificationRepositoryInterface
{
    private PaginatorInterface $pagination;

    public function __construct(
        PaginatorInterface $pagination,
        ManagerRegistry $registry
    ) {
        $this->pagination = $pagination;

        parent::__construct($registry, Notification::class);
    }

    /**
     * {@inhertiDoc}
     */
    public function findNotification(User $user, SearchRequest $request): Pagination
    {
        $queryBuilder = $this->createQueryBuilder('n')
            ->join('n.app', 'a')
            ->join('n.userNotification', 'un')
            ->join('un.user', 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $user->getId());

        $totalItems = $queryBuilder
            ->select('COUNT(n.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $queryBuilder->resetDQLPart('select');
        $queryResult = $queryBuilder
            ->select('n')
            ->orderBy('n.id', CommonConfig::ORDER)
            ->setFirstResult($request->offset())
            ->setMaxResults($request->getSize())
            ->getQuery()
            ->getResult();

        return $this->pagination->getResult(
            $queryResult,
            $totalItems,
            $request->getPage(),
            $request->getSize()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function save(Notification $notification): void
    {
        $this->getEntityManager()->persist($notification);
        $this->getEntityManager()->flush();
    }
}