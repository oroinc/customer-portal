<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\TestFrameworkBundle\Test\DataFixtures\InitialFixtureInterface;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

/**
 * Loads the first customer belongs to the first organization from the database.
 */
class LoadCustomer extends AbstractFixture implements DependentFixtureInterface, InitialFixtureInterface
{
    public const CUSTOMER = 'customer';

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadOrganization::class];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $businessUnit = $manager->getRepository(Customer::class)
            ->createQueryBuilder('t')
            ->where('t.organization = :organization')
            ->setParameter('organization', $this->getReference(LoadOrganization::ORGANIZATION))
            ->orderBy('t.id')
            ->getQuery()
            ->setMaxResults(1)
            ->getSingleResult();
        $this->addReference(self::CUSTOMER, $businessUnit);
    }
}
