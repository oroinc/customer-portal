<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MigrationBundle\Fixture\AbstractEntityReferenceFixture;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Migrations\Data\Demo\ORM\LoadAcmeOrganizationAndBusinessUnitData;

/**
 * Loads Customer demo data for Acme organization
 */
class LoadAcmeCustomerDemoData extends AbstractEntityReferenceFixture implements DependentFixtureInterface
{
    use CreateCustomerTrait;

    /** @var array */
    protected $customers = [
        'Company Acme' => [
            'group' => 'All Customers',
            'subsidiaries' => [
                'Company Acme - East Division' => [
                    'group' => 'All Customers',
                ],
                'Company Acme - West Division' => [
                    'group' => 'All Customers',
                ],
            ],
        ],
        'Customer BrandCo' => [
            'group' => 'All Customers',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadCustomerInternalRatingDemoData::class,
            LoadCustomerGroupDemoData::class,
            LoadAcmeOrganizationAndBusinessUnitData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $internalRatings = $this->getObjectReferencesByIds(
            $manager,
            ExtendHelper::buildEnumValueClassName(Customer::INTERNAL_RATING_CODE),
            LoadCustomerInternalRatingDemoData::getDataKeys()
        );

        /** @var \Oro\Bundle\UserBundle\Entity\User $customerOwner */
        $customerOwner = $manager->getRepository('OroUserBundle:User')->findOneBy([]);

        /** @var Organization $acmeOrganization */
        $acmeOrganization = $this->getReference(LoadAcmeOrganizationAndBusinessUnitData::REFERENCE_DEMO_ORGANIZATION);

        $customerGroup = $this->getReference(
            LoadCustomerGroupDemoData::ACCOUNT_GROUP_REFERENCE_SECOND_ORGANIZATION_PREFIX . 'All Customers'
        );

        foreach ($this->customers as $customerName => $customerData) {
            /** @var CustomerGroup $customerGroup */

            $rating = $internalRatings[array_rand($internalRatings)];
            $parent =
                $this->createCustomer(
                    $manager,
                    $customerName,
                    $customerOwner,
                    $customerGroup,
                    $rating,
                    $acmeOrganization
                );

            $this->addReference(LoadCustomerDemoData::ACCOUNT_REFERENCE_PREFIX . $parent->getName(), $parent);

            if (isset($customerData['subsidiaries'])) {
                foreach ($customerData['subsidiaries'] as $subsidiaryName => $subsidiaryData) {
                    $customer = $this->createCustomer(
                        $manager,
                        $subsidiaryName,
                        $customerOwner,
                        $customerGroup,
                        $rating,
                        $acmeOrganization,
                        $parent
                    );

                    $this->addReference(LoadCustomerDemoData::ACCOUNT_REFERENCE_PREFIX . $subsidiaryName, $customer);
                }
            }
        }

        $manager->flush();
    }
}
