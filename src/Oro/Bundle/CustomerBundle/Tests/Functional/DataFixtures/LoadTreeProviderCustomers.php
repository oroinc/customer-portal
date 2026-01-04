<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\UserBundle\Entity\User;

class LoadTreeProviderCustomers extends AbstractFixture implements DependentFixtureInterface
{
    public const CUSTOMER_LEVEL_1_2 = 'customer.level_1_2';
    public const CUSTOMER_LEVEL_1_2_DOT_1 = 'customer.level_1_2.1';
    public const CUSTOMER_LEVEL_1_2_DOT_1_DOT_1 = 'customer.level_1_2.1.1';

    #[\Override]
    public function getDependencies(): array
    {
        return [LoadCustomers::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        // This structure is necessary in order to disrupt ID order, thereby causing abnormal behavior of tree
        // processing, when the customer with a less ID may be child of customer with greater ID
        $levelThird = $this->createCustomer($manager, self::CUSTOMER_LEVEL_1_2_DOT_1_DOT_1);
        $levelSecond = $this->createCustomer($manager, self::CUSTOMER_LEVEL_1_2_DOT_1);
        $levelFirst = $this->createCustomer($manager, self::CUSTOMER_LEVEL_1_2);

        $levelThird->setParent($levelSecond);
        $levelSecond->setParent($levelFirst);

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param string $name
     *
     * @return Customer
     */
    protected function createCustomer(ObjectManager $manager, $name): Customer
    {
        /** @var User $owner */
        $owner = $this->getReference('user');

        $customer = new Customer();
        $customer->setName($name);
        $customer->setOwner($owner);
        $customer->setOrganization($owner->getOrganization());

        $manager->persist($customer);
        $this->addReference($name, $customer);

        return $customer;
    }
}
