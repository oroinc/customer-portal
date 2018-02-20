<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress;

abstract class AbstractAddressesFixture extends AbstractFixture
{
    /**
     * @param EntityManager $manager
     * @param array $addressData
     * @param AbstractDefaultTypedAddress $address
     */
    protected function addAddress(EntityManager $manager, array $addressData, AbstractDefaultTypedAddress $address)
    {
        $defaults = [];
        foreach ($addressData['types'] as $type => $isDefault) {
            /** @var AddressType $addressType */
            $addressType = $manager->getReference('Oro\Bundle\AddressBundle\Entity\AddressType', $type);
            $address->addType($addressType);
            if ($isDefault) {
                $defaults[] = $addressType;
            }
        }

        /** @var Country $country */
        $country = $manager->getReference('OroAddressBundle:Country', $addressData['country']);
        /** @var Region $region */
        $region = $manager->getReference(
            'OroAddressBundle:Region',
            $addressData['country'] . '-' . $addressData['region']
        );

        $address->setDefaults($defaults);
        $address->setPrimary($addressData['primary'])
            ->setLabel($addressData['label'])
            ->setStreet($addressData['street'])
            ->setCity($addressData['city'])
            ->setPostalCode($addressData['postalCode'])
            ->setCountry($country)
            ->setRegion($region)
            ->setOrganization('Test Org');

        $manager->persist($address);
        $this->addReference($addressData['label'], $address);
    }

    /**
     * @param ObjectManager $manager
     * @return \Oro\Bundle\OrganizationBundle\Entity\Organization
     */
    protected function getOrganization(ObjectManager $manager)
    {
        return $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
    }
}
