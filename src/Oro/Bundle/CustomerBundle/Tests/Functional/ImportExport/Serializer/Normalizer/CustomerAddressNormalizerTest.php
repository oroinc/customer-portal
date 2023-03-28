<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport\Serializer\Normalizer;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadFullCustomerAddress;

class CustomerAddressNormalizerTest extends AbstractCustomerAddressNormalizerTest
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadFullCustomerAddress::class]);

        $this->normalizer = $this->getContainer()->get('oro_customer.importexport.normalizer.customer_address');
        $this->normalizer->setSerializer($this->getContainer()->get('oro_importexport.serializer'));
    }

    public function supportsDataProvider(): array
    {
        return [
            ['customer.level_1.address_1_full', true],
            [new \stdClass(), false]
        ];
    }

    public function testNormalizeForTheCustomerEntity(): void
    {
        /** @var CustomerAddress $address */
        $address = $this->getReference('customer.level_1.address_1_full');
        $data = $this->normalizer->normalize($address, context: ['entityName' => Customer::class]);

        $this->assertEquals(
            array_merge([
                'label' => 'customer.level_1.address_1_full',
                'id' => $address->getId(),
                'frontendOwner' => [
                    'id' => $address->getFrontendOwner()->getId(),
                    'name' => 'customer.level_1'
                ],
            ], self::getColumnsForNormalizer()),
            $data
        );
    }

    public function testNormalizeForTheCustomerAddressEntity(): void
    {
        /** @var CustomerAddress $address */
        $address = $this->getReference('customer.level_1.address_1_full');
        $data = $this->normalizer->normalize($address, context: ['entityName' => CustomerAddress::class]);

        $this->assertEquals(
            array_merge([
                'label' => 'customer.level_1.address_1_full',
                'id' => $address->getId(),
                'frontendOwner' => [
                    'id' => $address->getFrontendOwner()->getId(),
                    'name' => 'customer.level_1'
                ],
                'Billing' => true,
                'Default Billing' => false,
                'Shipping' => true,
                'Default Shipping' => true
            ], self::getColumnsForNormalizer()),
            $data
        );
    }

    public function testDenormalize(): void
    {
        /** @var CustomerAddress $address */
        $address = $this->normalizer->denormalize(
            array_merge(
                self::getColumnsForNormalizer(),
                [
                    'id' => 11,
                    'label' => 'customer.level_1.address_1_full',
                    'frontendOwner' => [
                        'id' => 42,
                        'name' => 'customer.level_1'
                    ],
                    'Billing' => true,
                    'Default Billing' => false,
                    'Shipping' => true,
                    'Default Shipping' => true,
                ]
            ),
            CustomerAddress::class
        );

        $this->assertInstanceOf(CustomerAddress::class, $address);
        $this->assertEquals(11, $address->getId());

        $this->assertEquals('Test Org', $address->getOrganization());
        $this->assertEquals('Mr', $address->getNamePrefix());
        $this->assertEquals('First', $address->getFirstName());
        $this->assertEquals('Middle', $address->getMiddleName());
        $this->assertEquals('Last', $address->getLastName());
        $this->assertEquals('Sf', $address->getNameSuffix());
        $this->assertEquals('street2', $address->getStreet2());
        $this->assertEquals('+123321123', $address->getPhone());
        $this->assertTrue($address->isPrimary());
        $this->assertEquals('customer.level_1.address_1_full', $address->getLabel());
        $this->assertEquals('1215 Caldwell Road', $address->getStreet());
        $this->assertEquals('Rochester', $address->getCity());
        $this->assertEquals('14608', $address->getPostalCode());

        $this->assertEquals('US', $address->getCountry()->getIso2Code());
        $this->assertEquals('US-NY', $address->getRegion()->getCombinedCode());
        $this->assertEquals(42, $address->getFrontendOwner()->getId());
        $this->assertEquals('customer.level_1', $address->getFrontendOwner()->getName());
        $this->assertEquals('admin', $address->getOwner()->getUsername());

        $this->assertTrue($address->hasTypeWithName('billing'));
        $this->assertTrue($address->hasTypeWithName('shipping'));
        $this->assertFalse($address->hasDefault('billing'));
        $this->assertTrue($address->hasDefault('shipping'));
    }
}
