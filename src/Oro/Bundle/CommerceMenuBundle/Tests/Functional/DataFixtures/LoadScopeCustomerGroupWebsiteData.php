<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadGroups;
use Oro\Bundle\ScopeBundle\Entity\Scope;
use Oro\Bundle\WebsiteBundle\Tests\Functional\DataFixtures\LoadWebsiteData;

class LoadScopeCustomerGroupWebsiteData extends AbstractFixture implements DependentFixtureInterface
{
    const GROUP_1_WEBSITE_1_SCOPE = 'group_1_website_2_scope';

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            LoadGroups::class,
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
        $scope->setCustomerGroup($this->getReference(LoadGroups::GROUP1));
        $manager->persist($scope);
        $manager->flush();
        $this->setReference(self::GROUP_1_WEBSITE_1_SCOPE, $scope);
    }
}
