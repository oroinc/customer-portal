<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress;
use Oro\Bundle\MigrationBundle\Fixture\AbstractEntityReferenceFixture;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Abstract class for loading customer address demo data.
 */
abstract class AbstractLoadAddressDemoData extends AbstractEntityReferenceFixture implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /** @var ObjectRepository|EntityRepository */
    protected $countryRepository;

    /** @var ObjectRepository|EntityRepository */
    protected $regionRepository;

    /** @var ObjectRepository|EntityRepository */
    protected $addressTypeRepository;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->initRepositories();
    }

    /**
     * @param $data
     * @return AbstractDefaultTypedAddress
     */
    protected function createAddress($data)
    {
        /** @var Country $country */
        $country = $this->countryRepository->findOneBy(['iso2Code' => $data['country']]);
        if (!$country) {
            throw new \RuntimeException('Can\'t find country with ISO ' . $data['country']);
        }

        /** @var Region $region */
        $region = $this->regionRepository->findOneBy(['country' => $country, 'code' => $data['state']]);
        if (!$region) {
            throw new \RuntimeException(
                printf('Can\'t find region with country ISO %s and code %s', $data['country'], $data['state'])
            );
        }

        $types = [];
        $typesFromData = explode(',', $data['types']);
        foreach ($typesFromData as $type) {
            $types[] = $this->addressTypeRepository->find($type);
        }

        $defaultTypes = [];
        $defaultTypesFromData = explode(',', $data['defaultTypes']);
        foreach ($defaultTypesFromData as $defaultType) {
            $defaultTypes[] = $this->addressTypeRepository->find($defaultType);
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

    protected function initRepositories()
    {
        $doctrine = $this->container->get('doctrine');
        $this->countryRepository = $doctrine
            ->getManagerForClass(Country::class)
            ->getRepository(Country::class);

        $this->regionRepository = $doctrine
            ->getManagerForClass(Region::class)
            ->getRepository(Region::class);

        $this->addressTypeRepository = $doctrine
            ->getManagerForClass(AddressType::class)
            ->getRepository(AddressType::class);
    }

    /**
     * Return new entity compatible with AbstractDefaultTypedAddress
     *
     * @return AbstractDefaultTypedAddress
     */
    abstract protected function getNewAddressEntity();
}
