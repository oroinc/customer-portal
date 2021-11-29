<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\ScopeBundle\Manager\ScopeManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Loads ScopeCustomerGroup demo data
 */
class LoadScopeCustomerGroupDemoData extends AbstractFixture implements
    FixtureInterface,
    DependentFixtureInterface,
    ContainerAwareInterface
{
    use ContainerAwareTrait;

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
        $scopeManager = $this->container->get('oro_scope.scope_manager');
        foreach ($customerGroups as $customerGroup) {
            $scopeManager->findOrCreate(ScopeManager::BASE_SCOPE, ['customerGroup' => $customerGroup], true);
        }
    }
}
