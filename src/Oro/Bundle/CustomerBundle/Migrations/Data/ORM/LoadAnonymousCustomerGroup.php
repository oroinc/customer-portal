<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadRolesData;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
        $customerGroup = new CustomerGroup();
        $customerGroup->setName(self::GROUP_NAME_NON_AUTHENTICATED);

        $organizationRepository = $manager->getRepository(Organization::class);
        $defaultOrganization = $organizationRepository->getFirst();

        $customerGroup->setOrganization($defaultOrganization);

        $roleRepository = $manager->getRepository(Role::class);
        $role = $roleRepository->findOneBy(['role' => LoadRolesData::ROLE_ADMINISTRATOR]);
        $user = $roleRepository->getFirstMatchedUser($role);
        $customerGroup->setOwner($user);

        /** @var EntityManager $manager */
        $manager->persist($customerGroup);
        $manager->flush($customerGroup);

        /** @var ConfigManager $configManager */
        $configManager = $this->container->get('oro_config.global');
        $configManager->set('oro_customer.anonymous_customer_group', $customerGroup->getId());
        $configManager->flush();
    }
}
