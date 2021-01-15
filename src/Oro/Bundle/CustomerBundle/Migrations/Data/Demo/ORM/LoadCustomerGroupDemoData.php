<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\UserBundle\DataFixtures\UserUtilityTrait;

/**
 * Loads CustomerGroups data
 */
class LoadCustomerGroupDemoData extends AbstractFixture
{
    use UserUtilityTrait;

    const ACCOUNT_GROUP_REFERENCE_PREFIX = 'customer_group_demo_data';

    /**
     * @var array
     */
    protected $customerGroups = [
        'All Customers',
        'Wholesale Customers',
        'Partners',
        'Non-Authenticated Visitors'
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var \Oro\Bundle\UserBundle\Entity\User $customerOwner */
        $customerOwner = $this->getFirstUser($manager);

        foreach ($this->customerGroups as $groupName) {
            $customerGroup = $manager->getRepository('OroCustomerBundle:CustomerGroup')
                ->findOneBy(['name' => $groupName]);

            if (!$customerGroup) {
                $customerGroup = new CustomerGroup();
                $customerGroup
                    ->setName($groupName)
                    ->setOrganization($customerOwner->getOrganization())
                    ->setOwner($customerOwner);
                $manager->persist($customerGroup);
            }

            $this->addReference(static::ACCOUNT_GROUP_REFERENCE_PREFIX . $customerGroup->getName(), $customerGroup);
        }

        $manager->flush();
    }
}
