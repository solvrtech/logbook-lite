<?php

namespace App\Repository\Setting;

use App\Common\Config\MailConfig;
use App\Entity\MailSetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

abstract class SqlMailSettingRepository
    extends ServiceEntityRepository
    implements MailSettingRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, MailSetting::class);
    }

    /**
     * {@inheritDoc}
     *
     * @throws NonUniqueResultException
     */
    public function findMailSetting(): MailSetting|null
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder
            ->where('m.setting = :setting')
            ->setParameter('setting', MailConfig::GLOBAL_MAIL_SETTING);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritDoc}
     */
    public function save(MailSetting $mailSetting): void
    {
        $this->getEntityManager()->persist($mailSetting);
        $this->getEntityManager()->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $setting, ?int $appId): void
    {
        $queryBuilder = $this->createQueryBuilder('m')->delete();

        if (MailConfig::GLOBAL_MAIL_SETTING === $setting) {
            $queryBuilder
                ->where('m.setting = :setting')
                ->setParameter('setting', $setting);
        } else {
            $queryBuilder
                ->where('m.setting = :setting')
                ->andWhere('m.app = :appId')
                ->setParameters(
                    new ArrayCollection(array(
                        new Parameter('setting', $setting),
                        new Parameter('appId', $appId),
                    ))
                );
        }

        $queryBuilder->getQuery()->execute();
    }
}