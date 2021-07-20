<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

abstract class AbstractAddressesFixture extends AbstractFixture
{
    protected function addAddress(EntityManager $manager, array $addressData, AbstractDefaultTypedAddress $address)
    {
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

    /**
     * @param EntityManager $manager
     * @param string        $countryCode
     *
     * @return Country
     */
    protected function getCountry(EntityManager $manager, $countryCode)
    {
        $referenceName = 'country.' . $countryCode;
        if ($this->hasReference($referenceName)) {
            return $this->getReference($referenceName);
        }

        $country = $manager->getReference(Country::class, $countryCode);
        $this->addReference($referenceName, $country);

        return $country;
    }

    /**
     * @param EntityManager $manager
     * @param string        $regionCode
     *
     * @return Region
     */
    protected function getRegion(EntityManager $manager, $regionCode)
    {
        $referenceName = 'region.' . $regionCode;
        if ($this->hasReference($referenceName)) {
            return $this->getReference($referenceName);
        }

        $region = $manager->getReference(Region::class, $regionCode);
        $this->addReference($referenceName, $region);

        return $region;
    }

    /**
     * @param ObjectManager $manager
     *
     * @return Organization
     */
    protected function getOrganization(ObjectManager $manager)
    {
        return $manager->getRepository(Organization::class)->getFirst();
    }
}
