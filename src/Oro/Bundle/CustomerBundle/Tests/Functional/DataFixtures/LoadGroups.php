<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\UserBundle\DataFixtures\UserUtilityTrait;
use Oro\Bundle\UserBundle\Entity\User;

class LoadGroups extends AbstractFixture
{
    use UserUtilityTrait;

    const GROUP1 = 'customer_group.group1';
    const GROUP2 = 'customer_group.group2';
    const GROUP3 = 'customer_group.group3';

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $owner = $this->getFirstUser($manager);
        $this->createGroup($manager, self::GROUP1, $owner);
        $this->createGroup($manager, self::GROUP2, $owner);
        $this->createGroup($manager, self::GROUP3, $owner);

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
}
