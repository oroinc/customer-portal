<?php

namespace Oro\Bundle\CustomerBundle\Entity\Repository;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\UserBundle\Entity\Repository\AbstractUserRepository;

/**
 * Doctrine repository for Oro\Bundle\CustomerBundle\Entity\CustomerUser entity.
 */
class CustomerUserRepository extends AbstractUserRepository
{
    /**
     * {@inheritdoc}
     */
    protected function getQbForFindUserByEmail(string $email, bool $useLowercase): QueryBuilder
    {
        $qb = parent::getQbForFindUserByEmail($email, $useLowercase);
        $qb->andWhere($qb->expr()->eq('u.isGuest', ':isGuest'))
            ->setParameter('isGuest', false);

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    protected function getQbForFindLowercaseDuplicatedEmails(int $limit): QueryBuilder
    {
        $qb = parent::getQbForFindLowercaseDuplicatedEmails($limit);
        $qb->andWhere($qb->expr()->eq('u.isGuest', ':isGuest'))
            ->setParameter('isGuest', false);

        return $qb;
    }
}
