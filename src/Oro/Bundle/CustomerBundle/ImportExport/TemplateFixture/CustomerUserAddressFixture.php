<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\TemplateFixture;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Fixture of CustomerUserAddress entity used for generation of import-export template
 */
class CustomerUserAddressFixture extends AbstractTemplateRepository implements TemplateFixtureInterface
{
    #[\Override]
    public function getEntityClass(): string
    {
        return CustomerUserAddress::class;
    }

    #[\Override]
    public function fillEntityData($key, $entity): void
    {
        /** @var CustomerUserAddress $entity */
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
            ->setPostalCode('14608')
            ->setValidatedAt(new \DateTime('now', new \DateTimeZone('UTC')));

        $entity->setPhone('(+1) 212 123 4567');
        $entity->setTypes(new ArrayCollection([new AddressType('billing'), new AddressType('shipping')]));
        $entity->setDefaults(new ArrayCollection([new AddressType('billing'), new AddressType('shipping')]));
        $entity->setFrontendOwner($this->createFrontendOwner());
        $entity->setOwner($this->createOwner());
    }

    #[\Override]
    public function getData(): iterable
    {
        return $this->getEntityData('Example of Customer User Address');
    }

    #[\Override]
    protected function createEntity($key): object
    {
        return $this->setProperty(new CustomerUserAddress(), 'id', 1);
    }

    private function createFrontendOwner(): CustomerUser
    {
        $owner = new CustomerUser();
        $owner = $this->setProperty($owner, 'id', 2);
        $owner->setUsername('customer_user@example.com');
        $owner->setEmail('customer_user@example.com');

        return $owner;
    }

    private function createOwner(): User
    {
        $owner = new User();
        $owner = $this->setProperty($owner, 'id', 1);
        $owner->setUsername('admin_user');

        return $owner;
    }

    protected function setProperty(object $entity, string $name, mixed $value): object
    {
        $reflectionUser = new \ReflectionClass($entity);

        $property = $reflectionUser->getProperty($name);
        $property->setAccessible(true);
        $property->setValue($entity, $value);
        $property->setAccessible(false);

        return $entity;
    }
}
