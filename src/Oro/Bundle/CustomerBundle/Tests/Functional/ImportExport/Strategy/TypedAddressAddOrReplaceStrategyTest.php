<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport\Strategy;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\ImportExport\Strategy\TypedAddressAddOrReplaceStrategy;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadFullCustomerAddress;
use Oro\Bundle\ImportExportBundle\Context\Context;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\ReflectionUtil;

class TypedAddressAddOrReplaceStrategyTest extends WebTestCase
{
    private TypedAddressAddOrReplaceStrategy $strategy;
    private Context $context;

    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadFullCustomerAddress::class]);

        $this->strategy = $this->getContainer()
            ->get('oro_customer.importexport.strategy.customer_address.add_or_replace');

        /**
         * Strategy must know about entity name
         */
        $this->strategy->setEntityName(CustomerAddress::class);

        $this->context = new Context([]);
        $this->strategy->setImportExportContext($this->context);
    }

    public function testImportById()
    {
        $newLabel = 'Updated label';
        /** @var CustomerAddress $existingAddress */
        $existingAddress = $this->getReference('customer.level_1.address_1_full');
        $existingAddressId = $existingAddress->getId();
        $customerId = $existingAddress->getFrontendOwner()->getId();

        $itemData = $this->getItemData($existingAddress);
        $itemData['label'] = $newLabel;
        $this->context->setValue('itemData', $itemData);

        $owner = new Customer();
        ReflectionUtil::setId($owner, $existingAddress->getFrontendOwner()->getId());

        $importedEntity = new CustomerAddress();
        ReflectionUtil::setId($importedEntity, $existingAddress->getId());
        $importedEntity->setFrontendOwner($owner);
        // Updated value
        $this->fillImportedEntityByExistingEntity($importedEntity, $existingAddress);
        $importedEntity->setLabel($newLabel);

        /** @var CustomerAddress $processedEntity */
        $processedEntity = $this->strategy->process($importedEntity);

        $this->assertNotNull($processedEntity);
        $this->assertEquals($existingAddressId, $processedEntity->getId());
        $this->assertEquals('Updated label', $processedEntity->getLabel());
        $this->assertEquals($customerId, $processedEntity->getFrontendOwner()->getId());
        $this->assertTrue($processedEntity->hasTypeWithName(AddressType::TYPE_BILLING));
        $this->assertTrue($processedEntity->hasTypeWithName(AddressType::TYPE_SHIPPING));
        $this->assertTrue($processedEntity->hasDefault(AddressType::TYPE_BILLING));
        $this->assertFalse($processedEntity->hasDefault(AddressType::TYPE_SHIPPING));
    }

    public function testImportByIdentityValues()
    {
        $newLabel = 'Updated label';
        /** @var CustomerAddress $existingAddress */
        $existingAddress = $this->getReference('customer.level_1.address_1_full');
        $existingAddressId = $existingAddress->getId();
        $customerId = $existingAddress->getFrontendOwner()->getId();

        $itemData = $this->getItemData($existingAddress);
        $itemData['label'] = $newLabel;
        $this->context->setValue('itemData', $itemData);

        $owner = new Customer();
        $owner->setName($existingAddress->getFrontendOwner()->getName());

        $importedEntity = new CustomerAddress();
        $importedEntity->setFrontendOwner($owner);
        // Updated value
        $this->fillImportedEntityByExistingEntity($importedEntity, $existingAddress);
        $importedEntity->setLabel($newLabel);

        /** @var CustomerAddress $processedEntity */
        $processedEntity = $this->strategy->process($importedEntity);

        $this->assertNotNull($processedEntity);
        $this->assertEquals($existingAddressId, $processedEntity->getId());
        $this->assertEquals('Updated label', $processedEntity->getLabel());
        $this->assertEquals($customerId, $processedEntity->getFrontendOwner()->getId());
        $this->assertTrue($processedEntity->hasTypeWithName(AddressType::TYPE_BILLING));
        $this->assertTrue($processedEntity->hasTypeWithName(AddressType::TYPE_SHIPPING));
        $this->assertTrue($processedEntity->hasDefault(AddressType::TYPE_BILLING));
        $this->assertFalse($processedEntity->hasDefault(AddressType::TYPE_SHIPPING));
        $this->assertNotNull($processedEntity->getSystemOrganization());
    }

    public function testImportWithOwnerChange()
    {
        /** @var Customer $otherCustomer */
        $otherCustomer = $this->getReference('customer.level_1_1');
        /** @var CustomerAddress $existingAddress */
        $existingAddress = $this->getReference('customer.level_1.address_1_full');

        $itemData = $this->getItemData($existingAddress);
        $this->context->setValue('itemData', $itemData);

        $owner = new Customer();
        ReflectionUtil::setId($owner, $otherCustomer->getId());

        $importedEntity = new CustomerAddress();
        ReflectionUtil::setId($importedEntity, $existingAddress->getId());
        $importedEntity->setFrontendOwner($owner);
        // Updated value
        $this->fillImportedEntityByExistingEntity($importedEntity, $existingAddress);

        /** @var CustomerAddress $processedEntity */
        $processedEntity = $this->strategy->process($importedEntity);

        $this->assertNull($processedEntity);
        $this->assertEquals(1, $this->context->getErrorEntriesCount());
        $this->assertEquals(
            ['Error in row #0. The owner of the existing address should not be changed'],
            $this->context->getErrors()
        );
    }

    public function testImportSameAddressToAnotherOwner()
    {
        /** @var Customer $otherCustomer */
        $otherCustomer = $this->getReference('customer.level_1_1');
        /** @var CustomerAddress $existingAddress */
        $existingAddress = $this->getReference('customer.level_1.address_1_full');

        $itemData = $this->getItemData($existingAddress);
        $this->context->setValue('itemData', $itemData);

        $owner = new Customer();
        ReflectionUtil::setId($owner, $otherCustomer->getId());

        $importedEntity = new CustomerAddress();
        $importedEntity->setFrontendOwner($owner);
        // Updated value
        $this->fillImportedEntityByExistingEntity($importedEntity, $existingAddress);

        /** @var CustomerAddress $processedEntity */
        $processedEntity = $this->strategy->process($importedEntity);

        $this->assertNotNull($processedEntity);
        $this->assertNull($processedEntity->getId());
        $this->assertEquals($otherCustomer->getId(), $processedEntity->getFrontendOwner()->getId());
        $this->assertNotNull($processedEntity->getSystemOrganization());
        $this->assertSame(
            $otherCustomer->getOrganization()->getId(),
            $processedEntity->getSystemOrganization()->getId()
        );
    }

    public function testImportDelete()
    {
        /** @var CustomerAddress $existingAddress */
        $existingAddress = $this->getReference('customer.level_1.address_1_full');
        $existingAddressId = $existingAddress->getId();

        $itemData = $this->getItemData($existingAddress);
        $itemData['Delete'] = true;
        $this->context->setValue('itemData', $itemData);

        $owner = new Customer();
        ReflectionUtil::setId($owner, $existingAddress->getFrontendOwner()->getId());

        $importedEntity = new CustomerAddress();
        ReflectionUtil::setId($importedEntity, $existingAddress->getId());
        $importedEntity->setFrontendOwner($owner);
        // Updated value
        $this->fillImportedEntityByExistingEntity($importedEntity, $existingAddress);

        /** @var CustomerAddress $processedEntity */
        $processedEntity = $this->strategy->process($importedEntity);

        $this->assertNotNull($processedEntity);
        $this->assertEquals($existingAddressId, $processedEntity->getId());
        $this->assertEquals([$existingAddressId], $this->context->getValue('marked_for_removal'));
        $this->assertEquals(0, $this->context->getReplaceCount());
        $this->assertEquals(1, $this->context->getDeleteCount());
    }

    private function getItemData(CustomerAddress $existingAddress): array
    {
        return [
            'label' => $existingAddress->getLabel(),
            'organization' => $existingAddress->getOrganization(),
            'namePrefix' => $existingAddress->getNamePrefix(),
            'firstName' => $existingAddress->getFirstName(),
            'middleName' => $existingAddress->getMiddleName(),
            'lastName' => $existingAddress->getLastName(),
            'nameSuffix' => $existingAddress->getNameSuffix(),
            'street' => $existingAddress->getStreet(),
            'street2' => $existingAddress->getStreet2(),
            'postalCode' => $existingAddress->getPostalCode(),
            'city' => $existingAddress->getCity(),
            'regionText' => null,
            'region' => ['combinedCode' => $existingAddress->getRegion()->getCombinedCode()],
            'country' => ['iso2Code' => $existingAddress->getCountry()->getIso2Code()],
            'id' => $existingAddress->getId(),
            'phone' => $existingAddress->getPhone(),
            'primary' => $existingAddress->isPrimary(),
            'frontendOwner' => ['id' => $existingAddress->getFrontendOwner()->getId(), 'name' => null],
            'owner' => ['username' => $existingAddress->getOwner()->getUsername()],
            'Billing' => true,
            'Default Billing' => true,
            'Shipping' => true,
            'Default Shipping' => false,
            'Delete' => false
        ];
    }

    private function fillImportedEntityByExistingEntity(
        CustomerAddress $importedEntity,
        CustomerAddress $existingAddress
    ): void {
        $owner = new User();
        $owner->setUsername($existingAddress->getOwner()->getUsername());

        // Scalars
        $importedEntity->setLabel($existingAddress->getLabel());
        $importedEntity->setOrganization($existingAddress->getOrganization());
        $importedEntity->setNamePrefix($existingAddress->getNamePrefix());
        $importedEntity->setFirstName($existingAddress->getFirstName());
        $importedEntity->setMiddleName($existingAddress->getMiddleName());
        $importedEntity->setLastName($existingAddress->getLastName());
        $importedEntity->setNameSuffix($existingAddress->getNameSuffix());
        $importedEntity->setStreet($existingAddress->getStreet());
        $importedEntity->setStreet2($existingAddress->getStreet2());
        $importedEntity->setPostalCode($existingAddress->getPostalCode());
        $importedEntity->setCity($existingAddress->getCity());
        $importedEntity->setPrimary($existingAddress->isPrimary());
        $importedEntity->setPhone($existingAddress->getPhone());
        // Relations
        $importedEntity->setOwner($owner);
        $importedEntity->setCountry(new Country($existingAddress->getCountryIso2()));
        $importedEntity->setRegion(new Region($existingAddress->getRegion()->getCombinedCode()));
        // Types
        $importedEntity->setTypes(new ArrayCollection([new AddressType('billing'), new AddressType('shipping')]));
        $importedEntity->setDefaults(new ArrayCollection([new AddressType('billing')]));
    }
}
