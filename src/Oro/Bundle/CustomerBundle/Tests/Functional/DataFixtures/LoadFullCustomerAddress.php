<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

class LoadFullCustomerAddress extends AbstractAddressesFixture implements DependentFixtureInterface
{
    private array $addresses = [
        [
            'customer' => 'customer.level_1',
            'label' => 'customer.level_1.address_1_full',
            'namePrefix' => 'Mr',
            'firstName' => 'First',
            'middleName' => 'Middle',
            'lastName' => 'Last',
            'nameSuffix' => 'Sf',
            'street' => '1215 Caldwell Road',
            'street2' => 'street2',
            'city' => 'Rochester',
            'postalCode' => '14608',
            'country' => 'US',
            'region' => 'NY',
            'phone' => '+123321123',
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
            $address->setNamePrefix($addressData['namePrefix']);
            $address->setFirstName($addressData['firstName']);
            $address->setMiddleName($addressData['middleName']);
            $address->setLastName($addressData['lastName']);
            $address->setNameSuffix($addressData['nameSuffix']);
            $address->setStreet2($addressData['street2']);
            $address->setPhone($addressData['phone']);
            $customer = $this->getReference($addressData['customer']);
            $address->setFrontendOwner($customer);
            $address->setOwner($customer->getOwner());
            $this->addAddress($manager, $addressData, $address);
        }
        $manager->flush();
    }
}
