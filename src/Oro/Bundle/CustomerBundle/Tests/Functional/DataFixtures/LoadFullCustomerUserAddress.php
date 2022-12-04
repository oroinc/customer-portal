<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;

class LoadFullCustomerUserAddress extends AbstractAddressesFixture implements DependentFixtureInterface
{
    /**
     * @var array
     */
    protected $addresses = [
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

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadCustomerUser::class];
    }

    /**
     * @param EntityManager $manager
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->addresses as $addressData) {
            $organization = $this->getOrganization($manager);
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
