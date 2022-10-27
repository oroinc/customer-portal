<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport\Strategy\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\UserBundle\DataFixtures\UserUtilityTrait;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadTestUser extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;
    use UserUtilityTrait;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadOrganization::class];
    }

    public function load(ObjectManager $manager)
    {
        /** @var User $userWithMainOrganizationAccess */
        $userWithMainOrganizationAccess = $this->getFirstUser($manager);

        /** @var UserManager $userManager */
        $userManager = $this->container->get('oro_user.manager');

        /** @var Organization $organization */
        $organization = $this->getReference('organization');

        $role = $manager->getRepository(Role::class)
            ->findBy(['role' => 'ROLE_ADMINISTRATOR']);

        /** @var User $userWithoutMainOrganizationAccess */
        $userWithoutMainOrganizationAccess = $userManager->createUser();
        $userWithoutMainOrganizationAccess
            ->setUsername('test_user')
            ->setPlainPassword(uniqid())
            ->setFirstName('Simple')
            ->setLastName('User')
            ->addUserRole($role[0])
            ->setEmail('test@test.com')
            ->setOrganization($organization)
            ->setSalt('');

        $userManager->updateUser($userWithoutMainOrganizationAccess);

        $this->setReference('user_with_main_organization_access', $userWithMainOrganizationAccess);
        $this->setReference('user_without_main_organization_access', $userWithoutMainOrganizationAccess);
    }
}
