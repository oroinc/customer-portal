<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUserData extends AbstractFixture implements ContainerAwareInterface
{
    const USER1 = 'admin-user1';
    const USER2 = 'admin-user2';

    /** @var array */
    protected $users = [
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

    /** @var UserManager */
    protected $userManager;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->userManager = $container->get('oro_user.manager');
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var Organization $organization */
        $organization = $manager->getRepository(Organization::class)->getFirst();
        /** @var Role $role */
        $role = $manager->getRepository(Role::class)->findOneBy(['role' => User::ROLE_DEFAULT]);

        foreach ($this->users as $item) {
            /* @var User $user */
            $user = $this->userManager->createUser();
            $user->setUsername($item['username'])
                ->setEmail($item['email'])
                ->setFirstName($item['firstname'])
                ->setLastName($item['lastname'])
                ->setEnabled(true)
                ->setPlainPassword($item['email'])
                ->setOrganization($organization)
                ->addUserRole($role);

            $this->userManager->updateUser($user);

            $this->setReference($user->getUsername(), $user);
        }
    }
}
