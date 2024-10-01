<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

class LoadFullCustomerUserAddress extends AbstractAddressesFixture implements DependentFixtureInterface
{
    private array $addresses = [
        [
            'customer_user' => 'customer_user',
            'label' => 'customer_user.address_1_full',
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
        return [LoadCustomerUser::class, LoadOrganization::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        /** @var Organization $organization */
        $organization = $this->getReference(LoadOrganization::ORGANIZATION);
        foreach ($this->addresses as $addressData) {
            $address = new CustomerUserAddress();
            $address->setOrganization($organization->getName());
            $address->setSystemOrganization($organization);
            $address->setNamePrefix($addressData['namePrefix']);
            $address->setFirstName($addressData['firstName']);
            $address->setMiddleName($addressData['middleName']);
            $address->setLastName($addressData['lastName']);
            $address->setNameSuffix($addressData['nameSuffix']);
            $address->setStreet2($addressData['street2']);
            $address->setPhone($addressData['phone']);
            $customerUser = $this->getReference($addressData['customer_user']);
            $address->setFrontendOwner($customerUser);
            $address->setOwner($customerUser->getOwner());
            $this->addAddress($manager, $addressData, $address);
        }
        $manager->flush();
    }
}
