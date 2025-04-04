<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

class LoadCustomerAddresses extends AbstractAddressesFixture implements DependentFixtureInterface
{
    protected array $addresses = [
        [
            'customer' => 'customer.level_1',
            'label' => 'customer.level_1.address_1',
            'street' => '1215 Caldwell Road',
            'city' => 'Rochester',
            'postalCode' => '14608',
            'country' => 'US',
            'region' => 'NY',
            'primary' => true,
            'types' => ['billing' => false, 'shipping' => true]
        ],
        [
            'customer' => 'customer.level_1',
            'label' => 'customer.level_1.address_2',
            'street' => '2413 Capitol Avenue',
            'city' => 'Romney',
            'postalCode' => '47981',
            'country' => 'US',
            'region' => 'IN',
            'primary' => false,
            'types' => ['billing' => true]
        ],
        [
            'customer' => 'customer.level_1',
            'label' => 'customer.level_1.address_3',
            'street' => '722 Harvest Lane',
            'city' => 'Sedalia',
            'postalCode' => '65301',
            'country' => 'US',
            'region' => 'MO',
            'primary' => false,
            'types' => ['billing' => false, 'shipping' => false]
        ],
        [
            'customer' => 'customer.level_1',
            'label' => 'customer.level_1.address_4',
            'street' => '1167 Marion Drive',
            'city' => 'Winter Haven',
            'postalCode' => '33830',
            'country' => 'US',
            'region' => 'FL',
            'primary' => false,
            'types' => [],
            'defaults' => []
        ],
        [
            'customer' => 'customer.level_1.1',
            'label' => 'customer.level_1.1.address_1',
            'street' => '2849 Junkins Avenue',
            'city' => 'Albany',
            'postalCode' => '31707',
            'country' => 'US',
            'region' => 'GA',
            'primary' => true,
            'types' => ['billing' => false, 'shipping' => true]
        ]
    ];

    #[\Override]
    public function getDependencies(): array
    {
        return [LoadCustomers::class, LoadOrganization::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        /** @var Organization $organization */
        $organization = $this->getReference(LoadOrganization::ORGANIZATION);
        foreach ($this->addresses as $addressData) {
            $address = new CustomerAddress();
            $address->setOrganization($organization->getName());
            $address->setSystemOrganization($organization);
            $address->setFrontendOwner($this->getReference($addressData['customer']));
            $this->addAddress($manager, $addressData, $address);
        }
        $manager->flush();
    }
}
