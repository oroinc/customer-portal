<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomers;
use Oro\Bundle\ScopeBundle\Entity\Scope;
use Oro\Bundle\WebsiteBundle\Tests\Functional\DataFixtures\LoadWebsiteData;

class LoadScopeCustomerWebsiteData extends AbstractFixture implements DependentFixtureInterface
{
    const WEBSITE_1_CUSTOMER_1_SCOPE = 'website_1_customer_1_scope';

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            LoadCustomers::class,
            LoadWebsiteData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $scope = new Scope();
        $scope->setWebsite($this->getReference(LoadWebsiteData::WEBSITE1));
        $scope->setCustomer($this->getReference(LoadCustomers::CUSTOMER_LEVEL_1_1));
        $manager->persist($scope);
        $manager->flush();
        $this->setReference(self::WEBSITE_1_CUSTOMER_1_SCOPE, $scope);
    }
}
