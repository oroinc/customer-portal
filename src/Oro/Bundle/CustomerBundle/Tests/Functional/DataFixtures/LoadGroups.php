<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Migrations\Data\ORM\LoadAnonymousCustomerGroup;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadGroups extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    const GROUP1 = 'customer_group.group1';
    const GROUP2 = 'customer_group.group2';
    const GROUP3 = 'customer_group.group3';
    const ANONYMOUS_GROUP = 'customer_group.anonymous';

    /**
     * @var ContainerInterface
     */
    protected $container;

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
    public function getDependencies()
    {
        return [LoadUser::class];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var User $owner */
        $owner = $this->getReference('user');
        $this->createGroup($manager, self::GROUP1, $owner);
        $this->createGroup($manager, self::GROUP2, $owner);
        $this->createGroup($manager, self::GROUP3, $owner);
        $this->loadAnonymousGroup();

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param string $name
     * @param User $owner
     * @return CustomerGroup
     */
    protected function createGroup(ObjectManager $manager, $name, User $owner)
    {
        $group = new CustomerGroup();
        $group->setName($name)
            ->setOwner($owner)
            ->setOrganization($owner->getOrganization());
        $manager->persist($group);
        $this->addReference($name, $group);

        return $group;
    }

    protected function loadAnonymousGroup()
    {
        $group = $this->container->get('doctrine')->getRepository(CustomerGroup::class)
            ->findOneBy(['name' => LoadAnonymousCustomerGroup::GROUP_NAME_NON_AUTHENTICATED]);
        $this->addReference(self::ANONYMOUS_GROUP, $group);
    }
}
