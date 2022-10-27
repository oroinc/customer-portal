<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\Api\Frontend\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\AbstractAddressesFixture;

class LoadBayerCustomerUserProfileData extends AbstractAddressesFixture implements DependentFixtureInterface
{
    /**
     * @var array
     */
    protected $addresses = [
        [
            'customer_user' => 'customer_user',
            'label' => 'customer_user_address_1',
            'street' => '1215 Caldwell Road',
            'city' => 'Rochester',
            'postalCode' => '14608',
            'country' => 'US',
            'region' => 'NY',
            'primary' => true,
            'types' => ['billing' => false, 'shipping' => true]
        ],
        [
            'customer_user' => 'customer_user',
            'label' => 'customer_user_address_2',
            'street' => '2413 Capitol Avenue',
            'city' => 'Romney',
            'postalCode' => '47981',
            'country' => 'US',
            'region' => 'IN',
            'primary' => false,
            'types' => ['billing' => true]
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [LoadBuyerCustomerUserData::class];
    }

    /**
     * @param EntityManager $manager
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->addresses as $key => $addressData) {
            $address = new CustomerUserAddress();
            $address->setSystemOrganization($this->getOrganization($manager));
            $address->setFrontendOwner($this->getReference($addressData['customer_user']));
            $this->addAddress($manager, $addressData, $address);
        }

        $manager->flush();
    }
}
