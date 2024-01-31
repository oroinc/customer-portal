<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadUserData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    public const USER1 = 'admin-user1';
    public const USER2 = 'admin-user2';

    private array $users = [
        [
            'email' => 'admin-user1@example.com',
            'username' => self::USER1,
            'firstname' => 'AdminUser1FN',
            'lastname' => 'AdminUser1LN',
        ],
        [
            'email' => 'admin-user2@example.com',
            'username' => self::USER2,
            'firstname' => 'AdminUser2FN',
            'lastname' => 'AdminUser2LN',
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [LoadOrganization::class];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        $userManager = $this->container->get('oro_user.manager');
        /** @var Role $role */
        $role = $manager->getRepository(Role::class)->findOneBy(['role' => User::ROLE_DEFAULT]);
        foreach ($this->users as $item) {
            /* @var User $user */
            $user = $userManager->createUser();
            $user->setUsername($item['username'])
                ->setEmail($item['email'])
                ->setFirstName($item['firstname'])
                ->setLastName($item['lastname'])
                ->setEnabled(true)
                ->setPlainPassword($item['email'])
                ->setOrganization($this->getReference(LoadOrganization::ORGANIZATION))
                ->addUserRole($role);

            $userManager->updateUser($user);

            $this->setReference($user->getUserIdentifier(), $user);
        }
    }
}
