<?php

namespace App\Repository\App;

use App\Entity\TeamApp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

abstract class SqlTeamAppRepository
    extends ServiceEntityRepository
    implements TeamAppRepositoryInterface
{
    public Connection $connection;

    public function __construct(
        ManagerRegistry $registry,
        Connection $connection
    ) {
        $this->connection = $connection;

        parent::__construct($registry, TeamApp::class);
    }
}