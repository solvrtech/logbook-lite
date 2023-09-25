<?php

namespace App\Repository\Log;

use App\Entity\User;

class MysqlLogCommentRepository extends SqlLogCommentRepository
{
    /**
     * {@inheritDoc}
     */
    public function findComment(User $user, int|string $logId, int $appId): array
    {
        $queryBuilder = $this->createQueryBuilder('lc')
            ->join('lc.user', 'u')
            ->join('lc.app', 'a')
            ->where('a.id = :appId')
            ->andWhere('lc.logId = :logId')
            ->setParameter('appId', $appId)
            ->setParameter('logId', $logId);

        return $queryBuilder
            ->distinct('lc.id')
            ->select(
                'lc.id',
                'lc.comment',
                'lc.createdAt',
                "CASE WHEN lc.modifiedAt IS NOT NULL THEN true ELSE false END AS modified",
                'u.id AS userId',
                'u.name As userName',
                "CASE WHEN u.id = {$user->getId()} THEN true ELSE false END AS myComment"
            )
            ->getQuery()
            ->getArrayResult();
    }
}