<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Creates the customer and customer group entities.
 */
class LoadCustomerData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadUser::class];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var User $owner */
        $owner = $this->getReference('user');

        $customerGroup = $this->createCustomerGroup($manager, $owner);
        $this->addReference('customer_group', $customerGroup);
        $customer = $this->createCustomer($manager, $customerGroup, $owner);
        $this->addReference('customer', $customer);

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param User          $owner
     *
     * @return CustomerGroup
     */
    protected function createCustomerGroup(ObjectManager $manager, User $owner)
    {
        $customer = new CustomerGroup();
        $customer->setName('Customer Group');
        $customer->setOwner($owner);
        $customer->setOrganization($owner->getOrganization());
        $manager->persist($customer);

        return $customer;
    }

    /**
     * @param ObjectManager $manager
     * @param CustomerGroup $customerGroup
     * @param User          $owner
     *
     * @return Customer
     */
    protected function createCustomer(ObjectManager $manager, CustomerGroup $customerGroup, User $owner)
    {
        $customer = new Customer();
        $customer->setName('Customer');
        $customer->setGroup($customerGroup);
        $customer->setOwner($owner);
        $customer->setOrganization($owner->getOrganization());
        $manager->persist($customer);

        return $customer;
    }
}
