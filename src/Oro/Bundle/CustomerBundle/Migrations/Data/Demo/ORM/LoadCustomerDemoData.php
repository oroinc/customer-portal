<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MigrationBundle\Fixture\AbstractEntityReferenceFixture;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Loads Customers demo data for default organization
 */
class LoadCustomerDemoData extends AbstractEntityReferenceFixture implements DependentFixtureInterface
{
    use CreateCustomerTrait;

    public const ACCOUNT_REFERENCE_PREFIX = 'customer_demo_data';

    private array $customers = [
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

    #[\Override]
    public function getDependencies(): array
    {
        return [
            LoadCustomerInternalRatingDemoData::class,
            LoadCustomerGroupDemoData::class
        ];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $internalRatings = $this->getObjectReferencesByIds(
            $manager,
            EnumOption::class,
            $this->getEnumOptionIds()
        );

        /** @var User $customerOwner */
        $customerOwner = $manager->getRepository(User::class)->findOneBy([]);

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

    private function getEnumOptionIds(): array
    {
        return ExtendHelper::mapToEnumOptionIds(
            Customer::INTERNAL_RATING_CODE,
            LoadCustomerInternalRatingDemoData::getDataKeys()
        );
    }
}
