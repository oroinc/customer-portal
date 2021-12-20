<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\TestFrameworkBundle\Test\DataFixtures\InitialFixtureInterface;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

/**
 * Loads the first customer user belongs to the first customer and organization from the database.
 */
class LoadCustomerUser extends AbstractFixture implements DependentFixtureInterface, InitialFixtureInterface
{
    public const CUSTOMER_USER = 'customer_user';

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadOrganization::class, LoadCustomer::class];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $user = $manager->getRepository(CustomerUser::class)
            ->createQueryBuilder('t')
            ->where('t.organization = :organization AND t.customer = :customer')
            ->setParameter('organization', $this->getReference(LoadOrganization::ORGANIZATION))
            ->setParameter('customer', $this->getReference(LoadCustomer::CUSTOMER))
            ->orderBy('t.id')
            ->getQuery()
            ->setMaxResults(1)
            ->getSingleResult();
        $this->addReference(self::CUSTOMER_USER, $user);
    }
}
