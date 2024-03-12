<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress;

abstract class AbstractAddressesFixture extends AbstractFixture
{
    protected function addAddress(
        ObjectManager $manager,
        array $addressData,
        AbstractDefaultTypedAddress $address
    ): void {
        $defaults = [];
        foreach ($addressData['types'] as $type => $isDefault) {
            /** @var AddressType $addressType */
            $addressType = $manager->getReference(AddressType::class, $type);
            $address->addType($addressType);
            if ($isDefault) {
                $defaults[] = $addressType;
            }
        }

        $address->setDefaults($defaults);
        $address->setPrimary($addressData['primary'])
            ->setLabel($addressData['label'])
            ->setStreet($addressData['street'])
            ->setCity($addressData['city'])
            ->setPostalCode($addressData['postalCode'])
            ->setCountry($this->getCountry($manager, $addressData['country']))
            ->setRegion($this->getRegion($manager, $addressData['country'] . '-' . $addressData['region']))
            ->setOrganization('Test Org');

        $manager->persist($address);
        $this->addReference($addressData['label'], $address);
    }

    protected function getCountry(ObjectManager $manager, string $countryCode): Country
    {
        $referenceName = 'country.' . $countryCode;
        if ($this->hasReference($referenceName)) {
            return $this->getReference($referenceName);
        }

        $country = $manager->getReference(Country::class, $countryCode);
        $this->addReference($referenceName, $country);

        return $country;
    }

    protected function getRegion(ObjectManager $manager, string $regionCode): Region
    {
        $referenceName = 'region.' . $regionCode;
        if ($this->hasReference($referenceName)) {
            return $this->getReference($referenceName);
        }

        $region = $manager->getReference(Region::class, $regionCode);
        $this->addReference($referenceName, $region);

        return $region;
    }
}
