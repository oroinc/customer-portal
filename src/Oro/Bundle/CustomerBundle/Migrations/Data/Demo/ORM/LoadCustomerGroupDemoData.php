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

    public const ACCOUNT_GROUP_REFERENCE_PREFIX = 'customer_group_demo_data';

    private array $customerGroups = [
        'All Customers',
        'Wholesale Customers',
        'Partners',
        'Non-Authenticated Visitors'
    ];

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        $customerOwner = $this->getFirstUser($manager);

        foreach ($this->customerGroups as $groupName) {
            $customerGroup = $manager->getRepository(CustomerGroup::class)
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
