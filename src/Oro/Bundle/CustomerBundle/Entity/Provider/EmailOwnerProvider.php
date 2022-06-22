<?php

namespace Oro\Bundle\CustomerBundle\Entity\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;
use Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface;

/**
 * The email address owner provider for CustomerUser entity.
 */
class EmailOwnerProvider implements EmailOwnerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEmailOwnerClass(): string
    {
        return CustomerUser::class;
    }

    /**
     * {@inheritdoc}
     */
    public function findEmailOwner(EntityManagerInterface $em, string $email): ?EmailOwnerInterface
    {
        $results = $em->getRepository(CustomerUser::class)
            ->findBy(['emailLowercase' => mb_strtolower($email)], null, 1);

        return array_shift($results);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizations(EntityManagerInterface $em, string $email): array
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
    public function getEmails(EntityManagerInterface $em, int $organizationId): iterable
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
