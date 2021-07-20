<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MigrationBundle\Fixture\AbstractEntityReferenceFixture;

/**
 * Loads Customers demo data for default organization
 */
class LoadCustomerDemoData extends AbstractEntityReferenceFixture implements DependentFixtureInterface
{
    use CreateCustomerTrait;

    const ACCOUNT_REFERENCE_PREFIX = 'customer_demo_data';

    /** @var array */
    protected $customers = [
        'Company A' => [
            'group' => 'All Customers',
            'subsidiaries' => [
                'Company A - East Division' => [
                    'group' => 'All Customers',
                ],
                'Company A - West Division' => [
                    'group' => 'All Customers',
                ],
            ],
        ],
        'Wholesaler B' => [
            'group' => 'Wholesale Customers',
        ],
        'Partner C' => [
            'group' => 'Partners',
        ],
        'Customer G' => [
            'group' => 'All Customers',
        ],
        'Anonymous 1' => [
            'group' => 'Non-Authenticated Visitors',
        ],
        'Anonymous 2' => [
            'group' => 'Non-Authenticated Visitors',
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

        foreach ($this->customers as $customerName => $customerData) {
            /** @var CustomerGroup $customerGroup */
            $customerGroup = $this->getReference(
                LoadCustomerGroupDemoData::ACCOUNT_GROUP_REFERENCE_PREFIX . $customerData['group']
            );

            $parent =
                $this->createCustomer(
                    $manager,
                    $customerName,
                    $customerOwner,
                    $customerGroup,
                    $internalRatings[array_rand($internalRatings)],
                    $customerOwner->getOrganization()
                );

            $this->addReference(static::ACCOUNT_REFERENCE_PREFIX . $parent->getName(), $parent);

            if (isset($customerData['subsidiaries'])) {
                foreach ($customerData['subsidiaries'] as $subsidiaryName => $subsidiaryData) {
                    /** @var CustomerGroup $subsidiaryGroup */
                    $subsidiaryGroup = $this->getReference(
                        LoadCustomerGroupDemoData::ACCOUNT_GROUP_REFERENCE_PREFIX . $subsidiaryData['group']
                    );
                    $subsidiary =
                        $this->createCustomer(
                            $manager,
                            $subsidiaryName,
                            $customerOwner,
                            $subsidiaryGroup,
                            $internalRatings[array_rand($internalRatings)],
                            $customerOwner->getOrganization(),
                            $parent
                        );

                    $this->addReference(static::ACCOUNT_REFERENCE_PREFIX . $subsidiary->getName(), $subsidiary);
                }
            }
        }

        $manager->flush();
    }
}
