<?php

namespace App\Repository\Team;

use App\Common\QueryBuilder;
use App\Entity\User;
use App\Entity\UserTeam;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

abstract class SqlUserTeamRepository
    extends ServiceEntityRepository
    implements UserTeamRepositoryInterface
{
    public Connection $connection;

    public function __construct(
        ManagerRegistry $registry,
        Connection $connection
    ) {
        $this->connection = $connection;

        parent::__construct($registry, UserTeam::class);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function removeUser(User $user): void
    {
        $query = (new QueryBuilder())
            ->table('user_team ut')
            ->where("ut.user_id = {$user->getId()}")
            ->select('DELETE')
            ->build();

        $this->connection->executeQuery($query);
    }
}