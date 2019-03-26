<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\OrganizationBundle\Migrations\Data\Demo\ORM\LoadAcmeOrganizationAndBusinessUnitData;
use Oro\Bundle\UserBundle\DataFixtures\UserUtilityTrait;

/**
 * Loads CustomerGroups data
 */
class LoadCustomerGroupDemoData extends AbstractFixture implements DependentFixtureInterface
{
    use UserUtilityTrait;

    const ACCOUNT_GROUP_REFERENCE_PREFIX = 'customer_group_demo_data';
    const ACCOUNT_GROUP_REFERENCE_SECOND_ORGANIZATION_PREFIX = 'customer_group_demo_data_second';

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
    public function getDependencies()
    {
        return [LoadAcmeOrganizationAndBusinessUnitData::class];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var \Oro\Bundle\UserBundle\Entity\User $customerOwner */
        $customerOwner = $this->getFirstUser($manager);
        $acmeOrganization = $this->getReference(LoadAcmeOrganizationAndBusinessUnitData::REFERENCE_DEMO_ORGANIZATION);

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

                $secondOrganizationGroup = new CustomerGroup();
                $secondOrganizationGroup
                    ->setName($groupName)
                    ->setOrganization($acmeOrganization)
                    ->setOwner($customerOwner);
                $manager->persist($secondOrganizationGroup);
                $this->addReference(
                    static::ACCOUNT_GROUP_REFERENCE_SECOND_ORGANIZATION_PREFIX . $secondOrganizationGroup->getName(),
                    $secondOrganizationGroup
                );
            }

            $this->addReference(static::ACCOUNT_GROUP_REFERENCE_PREFIX . $customerGroup->getName(), $customerGroup);
        }

        // Non-Authenticated Visitors loaded for second organization separately because it is not loaded in loop
        $secondOrganizationGroup = new CustomerGroup();
        $secondOrganizationGroup
            ->setName('Non-Authenticated Visitors')
            ->setOrganization($acmeOrganization)
            ->setOwner($customerOwner);
        $manager->persist($secondOrganizationGroup);

        $this->addReference(
            static::ACCOUNT_GROUP_REFERENCE_SECOND_ORGANIZATION_PREFIX . $secondOrganizationGroup->getName(),
            $secondOrganizationGroup
        );

        $manager->flush();
    }
}
