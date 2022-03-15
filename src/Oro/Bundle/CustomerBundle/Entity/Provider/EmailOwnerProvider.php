<?php

namespace Oro\Bundle\CustomerBundle\Entity\Provider;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface;

/**
 * The email address owner provider for CustomerUser entity.
 */
class EmailOwnerProvider implements EmailOwnerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEmailOwnerClass()
    {
        return CustomerUser::class;
    }

    /**
     * {@inheritdoc}
     */
    public function findEmailOwner(EntityManager $em, $email)
    {
        return $em->getRepository(CustomerUser::class)->findOneBy(['emailLowercase' => mb_strtolower($email)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizations(EntityManager $em, $email)
    {
        $rows = $em->createQueryBuilder()
            ->from(CustomerUser::class, 'cu')
            ->select('IDENTITY(cu.organization) AS id')
            ->where('cu.emailLowercase = :email')
            ->setParameter('email', mb_strtolower($email))
            ->getQuery()
            ->getArrayResult();

        $result = [];
        foreach ($rows as $row) {
            $result[] = (int)$row['id'];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmails(EntityManager $em, $organizationId)
    {
        $qb = $em->createQueryBuilder()
            ->from(CustomerUser::class, 'cu')
            ->select('cu.email')
            ->where('cu.organization = :organizationId')
            ->setParameter('organizationId', $organizationId)
            ->orderBy('cu.id');
        $iterator = new BufferedQueryResultIterator($qb);
        $iterator->setBufferSize(5000);
        foreach ($iterator as $row) {
            yield $row['email'];
        }
    }
}
