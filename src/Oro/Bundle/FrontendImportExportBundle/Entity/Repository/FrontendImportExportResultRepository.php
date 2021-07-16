<?php

namespace Oro\Bundle\FrontendImportExportBundle\Entity\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityRepository;

/**
 * Repository for FrontendImportExportResult entity.
 */
class FrontendImportExportResultRepository extends EntityRepository
{
    public function updateExpiredRecords(\DateTime $from, \DateTime $to): void
    {
        $qb = $this->createQueryBuilder('importExportResult');
        $qb->update()
            ->set('importExportResult.expired', ':expired')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->gte('importExportResult.createdAt', ':from'),
                    $qb->expr()->lte('importExportResult.createdAt', ':to')
                )
            )
            ->setParameter('expired', true, Types::BOOLEAN)
            ->setParameter('from', $from, Types::DATETIME_MUTABLE)
            ->setParameter('to', $to, Types::DATETIME_MUTABLE);

        $qb->getQuery()->execute();
    }
}
