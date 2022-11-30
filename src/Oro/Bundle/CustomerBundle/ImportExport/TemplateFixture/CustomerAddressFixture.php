<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\TemplateFixture;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Fixture of CustomerAddress entity used for generation of import-export template
 */
class CustomerAddressFixture extends AbstractTemplateRepository implements TemplateFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return CustomerAddress::class;
    }

    /**
     * {@inheritdoc}
     */
    public function fillEntityData($key, $entity)
    {
        /** @var CustomerAddress $entity */
        $entity->setPrimary(true)
            ->setLabel('Headquarters')
            ->setNamePrefix('Mr.')
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setNameSuffix('Jr.')
            ->setOrganization('Company A')
            ->setCountry(new Country('US'))
            ->setStreet('23400 Caldwell Road')
            ->setCity('Rochester')
            ->setRegion(new Region('US-NY'))
            ->setPostalCode('14608');

        $entity->setPhone('(+1) 212 123 4567');
        $entity->setTypes(new ArrayCollection([new AddressType('billing'), new AddressType('shipping')]));
        $entity->setDefaults(new ArrayCollection([new AddressType('billing'), new AddressType('shipping')]));
        $entity->setFrontendOwner($this->createFrontendOwner());
        $entity->setOwner($this->createOwner());
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->getEntityData('Example of Customer Address');
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($key)
    {
        return $this->setProperty(new CustomerAddress(), 'id', 1);
    }

    /**
     * @return Customer
     */
    private function createFrontendOwner(): Customer
    {
        $customer = new Customer();
        $customer = $this->setProperty($customer, 'id', 1);
        $customer->setName('Company A - East Division');

        return $customer;
    }

    private function createOwner(): User
    {
        $owner = new User();
        $owner = $this->setProperty($owner, 'id', 1);
        $owner->setUsername('admin_user');

        return $owner;
    }

    /**
     * @param object $entity
     * @param string $name
     * @param mixed $value
     * @return object
     */
    protected function setProperty($entity, string $name, $value)
    {
        $reflectionUser = new \ReflectionClass($entity);

        $property = $reflectionUser->getProperty($name);
        $property->setAccessible(true);
        $property->setValue($entity, $value);
        $property->setAccessible(false);

        return $entity;
    }
}
