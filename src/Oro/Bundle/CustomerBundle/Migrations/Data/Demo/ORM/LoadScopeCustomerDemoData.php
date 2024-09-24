<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\ScopeBundle\Entity\Scope;

/**
 * Loads scopes for customers.
 */
class LoadScopeCustomerDemoData extends AbstractFixture implements DependentFixtureInterface
{
    public const SCOPE_ACCOUNT_REFERENCE_PREFIX = 'scope_customer_demo_data';

    #[\Override]
    public function getDependencies(): array
    {
        return [LoadCustomerDemoData::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        /** @var Customer $customer */
        $customers = $manager->getRepository(Customer::class)->findAll();
        foreach ($customers as $customer) {
            $scope = new Scope();
            $scope->setCustomer($customer);
            $this->addReference(static::SCOPE_ACCOUNT_REFERENCE_PREFIX . $customer->getName(), $scope);
            $manager->persist($scope);
        }
        $manager->flush();
    }
}
