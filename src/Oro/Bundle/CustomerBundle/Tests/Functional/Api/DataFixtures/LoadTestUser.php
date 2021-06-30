<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadTestUser extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadOrganization::class];
    }

    public function load(ObjectManager $manager)
    {
        /** @var UserManager $userManager */
        $userManager = $this->container->get('oro_user.manager');

        $role = $manager->getRepository(Role::class)
            ->findBy(['role' => 'ROLE_ADMINISTRATOR']);

        $organization = $this->getReference('organization');

        $user = $userManager->createUser();
        $user
            ->setUsername('test_user')
            ->setPlainPassword(uniqid())
            ->setFirstName('Simple')
            ->setLastName('User')
            ->addUserRole($role[0])
            ->setEmail('test@test.com')
            ->setOrganization($organization)
            ->addOrganization($organization)
            ->setSalt('');

        $userManager->updateUser($user);
        $this->setReference('testUser', $user);
    }
}
