<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress;
use Oro\Bundle\MigrationBundle\Fixture\AbstractEntityReferenceFixture;
use Oro\Component\DependencyInjection\ContainerAwareInterface;
use Oro\Component\DependencyInjection\ContainerAwareTrait;

/**
 * The base class for fixtures that load customer and customer user addresses.
 */
abstract class AbstractLoadAddressDemoData extends AbstractEntityReferenceFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected function createAddress(array $data): AbstractDefaultTypedAddress
    {
        /** @var Country $country */
        $country = $this->container->get('doctrine')->getRepository(Country::class)
            ->findOneBy(['iso2Code' => $data['country']]);
        if (!$country) {
            throw new \RuntimeException('Can\'t find country with ISO ' . $data['country']);
        }

        /** @var Region $region */
        $region = $this->container->get('doctrine')->getRepository(Region::class)
            ->findOneBy(['country' => $country, 'code' => $data['state']]);
        if (!$region) {
            throw new \RuntimeException(
                printf('Can\'t find region with country ISO %s and code %s', $data['country'], $data['state'])
            );
        }

        $types = [];
        $typesFromData = explode(',', $data['types']);
        foreach ($typesFromData as $type) {
            $types[] = $this->container->get('doctrine')->getRepository(AddressType::class)->find($type);
        }

        $defaultTypes = [];
        $defaultTypesFromData = explode(',', $data['defaultTypes']);
        foreach ($defaultTypesFromData as $defaultType) {
            $defaultTypes[] = $this->container->get('doctrine')->getRepository(AddressType::class)->find($defaultType);
        }

        $address = $this->getNewAddressEntity();
        $address->setTypes(new ArrayCollection($types));
        $address->setDefaults(new ArrayCollection($defaultTypes))
            ->setPrimary(true)
            ->setLabel('Primary address')
            ->setCountry($country)
            ->setStreet($data['street'])
            ->setCity($data['city'])
            ->setRegion($region)
            ->setPostalCode($data['zipCode'])
            ->setFirstName($data['firstName'])
            ->setLastName($data['lastName']);

        return $address;
    }

    abstract protected function getNewAddressEntity(): AbstractDefaultTypedAddress;
}
