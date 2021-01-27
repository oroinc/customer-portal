<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\ScopeBundle\Entity\Scope;

/**
 * Loads ScopeCustomerGroup demo data
 */
class LoadScopeCustomerGroupDemoData extends AbstractFixture implements FixtureInterface, DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadCustomerGroupDemoData::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var CustomerGroup $customerGroup */
        $customerGroups = $manager->getRepository('OroCustomerBundle:CustomerGroup')->findAll();
        foreach ($customerGroups as $customerGroup) {
            $scope = new Scope();
            $scope->setCustomerGroup($customerGroup);
            $manager->persist($scope);
        }

        $manager->flush();
    }
}
