<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;

class LoadCustomerUserAddresses extends AbstractAddressesFixture implements DependentFixtureInterface
{
    const OTHER_USER_LABEL = 'other.user@test.com.address_1';

    /**
     * @var array
     */
    protected $addresses = [
        [
            'customer_user' => 'grzegorz.brzeczyszczykiewicz@example.com',
            'label' => 'grzegorz.brzeczyszczykiewicz@example.com.address_1',
            'street' => '1215 Caldwell Road',
            'city' => 'Rochester',
            'postalCode' => '14608',
            'country' => 'US',
            'region' => 'NY',
            'primary' => true,
            'types' => ['billing' => false, 'shipping' => true]
        ],
        [
            'customer_user' => 'grzegorz.brzeczyszczykiewicz@example.com',
            'label' => 'grzegorz.brzeczyszczykiewicz@example.com.address_2',
            'street' => '2413 Capitol Avenue',
            'city' => 'Romney',
            'postalCode' => '47981',
            'country' => 'US',
            'region' => 'IN',
            'primary' => false,
            'types' => ['billing' => true]
        ],
        [
            'customer_user' => 'grzegorz.brzeczyszczykiewicz@example.com',
            'label' => 'grzegorz.brzeczyszczykiewicz@example.com.address_3',
            'street' => '722 Harvest Lane',
            'city' => 'Sedalia',
            'postalCode' => '65301',
            'country' => 'US',
            'region' => 'MO',
            'primary' => false,
            'types' => ['billing' => false, 'shipping' => false]
        ],
        [
            'customer_user' => 'grzegorz.brzeczyszczykiewicz@example.com',
            'label' => 'grzegorz.brzeczyszczykiewicz@example.com.address_4',
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
            'customer_user' => 'other.user@test.com',
            'label' => self::OTHER_USER_LABEL,
            'street' => '2849 Junkins Avenue',
            'city' => 'Albany',
            'postalCode' => '31707',
            'country' => 'US',
            'region' => 'GA',
            'primary' => true,
            'types' => ['billing' => false, 'shipping' => true]
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadCustomerUserData'
        ];
    }

    /**
     * @param EntityManager $manager
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->addresses as $addressData) {
            $address = new CustomerUserAddress();
            $address->setSystemOrganization($this->getOrganization($manager));
            $address->setFrontendOwner($this->getReference($addressData['customer_user']));
            $this->addAddress($manager, $addressData, $address);
        }

        $manager->flush();
    }
}
