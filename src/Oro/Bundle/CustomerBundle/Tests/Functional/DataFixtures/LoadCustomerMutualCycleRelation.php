<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Oro\Bundle\UserBundle\Entity\User;

class LoadCustomerMutualCycleRelation extends AbstractFixture implements DependentFixtureInterface
{
    public const string CUSTOMER_CYCLE_A = 'customer.cycle.a';
    public const string CUSTOMER_CYCLE_B = 'customer.cycle.b';

    #[\Override]
    public function getDependencies(): array
    {
        return [LoadUser::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        /** @var User $owner */
        $owner = $this->getReference('user');

        $customerA = $this->createCustomer(self::CUSTOMER_CYCLE_A, $owner);
        $manager->persist($customerA);

        $customerB = $this->createCustomer(self::CUSTOMER_CYCLE_B, $owner);
        $customerB->setParent($customerA);
        $manager->persist($customerB);

        $customerA->setParent($customerB);

        $manager->flush();

        $this->addReference(self::CUSTOMER_CYCLE_A, $customerA);
        $this->addReference(self::CUSTOMER_CYCLE_B, $customerB);
    }

    private function createCustomer(string $name, User $owner): Customer
    {
        $customer = new Customer();
        $customer->setName($name);
        $customer->setOwner($owner);
        $customer->setOrganization($owner->getOrganization());

        return $customer;
    }
}
