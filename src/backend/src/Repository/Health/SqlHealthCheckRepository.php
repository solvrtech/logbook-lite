<?php

namespace App\Repository\Health;

use App\Common\DateTimeHelper;
use App\Entity\HealthCheck;
use App\Service\Search\SearchResultServiceInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

abstract class SqlHealthCheckRepository extends
    ServiceEntityRepository
    implements HealthCheckRepositoryInterface
{
    public Connection $connection;
    public DateTimeHelper $format;
    public SearchResultServiceInterface $resultService;

    public function __construct(
        DateTimeHelper $format,
        Connection $connection,
        SearchResultServiceInterface $resultService,
        ManagerRegistry $registry
    ) {
        $this->connection = $connection;
        $this->format = $format;
        $this->resultService = $resultService;

        parent::__construct($registry, HealthCheck::class);
    }

    /**
     * {@inheritDoc}
     */
    public function bulkSave(array $checks): void
    {
        $batchSize = 10;
        $index = 0;

        foreach ($checks as $check) {
            $this->getEntityManager()->persist($check);

            if (($index % $batchSize) === 0) {
                $this->getEntityManager()->flush();
            }

            $index++;
        }

        $this->getEntityManager()->flush();
    }
}