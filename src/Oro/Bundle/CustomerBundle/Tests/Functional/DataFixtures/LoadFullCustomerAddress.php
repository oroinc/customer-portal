<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;

class LoadFullCustomerAddress extends AbstractAddressesFixture implements DependentFixtureInterface
{
    /**
     * @var array
     */
    protected $addresses = [
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

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadCustomers::class];
    }

    /**
     * @param EntityManager $manager
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->addresses as $addressData) {
            $organization = $this->getOrganization($manager);
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
