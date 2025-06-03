<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Set the configuration 'oro_customer.anonymous_customer_group' at the application level.
 */
class LoadAnonymousCustomerGroup extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /**
     * @var string
     */
    const GROUP_NAME_NON_AUTHENTICATED = 'Non-Authenticated Visitors';

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadOrganizationAndBusinessUnitData::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $organizationRepository = $manager->getRepository(Organization::class);
        $defaultOrganization = $organizationRepository->getFirst();
        $customerGroup = $this->getCustomerGroup($manager, $defaultOrganization);

        # Global configuration is required if the application is used without authorization (from a terminal, etc.).
        $configManager = $this->container->get('oro_config.global');
        $configManager->set('oro_customer.anonymous_customer_group', $customerGroup->getId());
        $configManager->flush();
    }

    private function getCustomerGroup(
        ObjectManager $manager,
        OrganizationInterface $organization
    ): ?CustomerGroup {
        $repository = $manager->getRepository(CustomerGroup::class);

        return $repository->findOneBy([
            'organization' => $organization,
            'name' => LoadAnonymousCustomerGroup::GROUP_NAME_NON_AUTHENTICATED
        ]);
    }
}
