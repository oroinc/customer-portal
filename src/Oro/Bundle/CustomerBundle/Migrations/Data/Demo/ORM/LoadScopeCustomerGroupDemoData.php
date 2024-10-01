<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\ScopeBundle\Manager\ScopeManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Loads ScopeCustomerGroup demo data
 */
class LoadScopeCustomerGroupDemoData extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
    use ContainerAwareTrait;

    #[\Override]
    public function getDependencies(): array
    {
        return [LoadCustomerGroupDemoData::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        /** @var CustomerGroup $customerGroup */
        $customerGroups = $manager->getRepository(CustomerGroup::class)->findAll();
        $scopeManager = $this->container->get('oro_scope.scope_manager');
        foreach ($customerGroups as $customerGroup) {
            $scopeManager->findOrCreate(ScopeManager::BASE_SCOPE, ['customerGroup' => $customerGroup]);
        }
    }
}
