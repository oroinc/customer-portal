<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport\Serializer\Normalizer;

use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\ImportExport\Serializer\Normalizer\CustomerAddressNormalizer;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadFullCustomerAddress;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\Testing\Unit\EntityTrait;

class CustomerAddressNormalizerTest extends WebTestCase
{
    use EntityTrait;

    private CustomerAddressNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadFullCustomerAddress::class]);

        $this->normalizer = $this->getContainer()->get('oro_customer.importexport.normalizer.customer_address');
        $this->normalizer->setSerializer($this->getContainer()->get('oro_importexport.serializer'));
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupportsNormalization($data, bool $expected)
    {
        if (is_string($data)) {
            $data = $this->getReference($data);
        }
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data));
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupportsDenormalization($data, bool $expected)
    {
        if (is_string($data)) {
            $data = $this->getReference($data);
        }
        $this->assertSame($expected, $this->normalizer->supportsDenormalization([], get_class($data)));
    }

    public function supportsDataProvider(): \Generator
    {
        yield [
            'customer.level_1.address_1_full',
            true
        ];

        yield [
            new \stdClass(),
            false
        ];
    }

    public function testNormalize()
    {
        /** @var CustomerAddress $address */
        $address = $this->getReference('customer.level_1.address_1_full');
        $data = $this->normalizer->normalize($address);
        $this->assertEquals(
            [
                'city' => 'Rochester',
                'firstName' => 'First',
                'id' => $address->getId(),
                'label' => 'customer.level_1.address_1_full',
                'lastName' => 'Last',
                'middleName' => 'Middle',
                'namePrefix' => 'Mr',
                'nameSuffix' => 'Sf',
                'organization' => 'Test Org',
                'phone' => '+123321123',
                'postalCode' => '14608',
                'primary' => true,
                'regionText' => null,
                'street' => '1215 Caldwell Road',
                'street2' => 'street2',
                'country' => [
                    'iso2Code' => 'US'
                ],
                'frontendOwner' => [
                    'id' => $address->getFrontendOwner()->getId(),
                    'name' => 'customer.level_1'
                ],
                'owner' => [
                    'username' => 'admin'
                ],
                'region' => [
                    'combinedCode' => 'US-NY'
                ],
                'Billing' => true,
                'Default Billing' => false,
                'Shipping' => true,
                'Default Shipping' => true,
            ],
            $data
        );
    }

    public function testDenormalize()
    {
        /** @var CustomerAddress $address */
        $address = $this->normalizer->denormalize(
            [
                'city' => 'Rochester',
                'firstName' => 'First',
                'id' => 11,
                'label' => 'customer.level_1.address_1_full',
                'lastName' => 'Last',
                'middleName' => 'Middle',
                'namePrefix' => 'Mr',
                'nameSuffix' => 'Sf',
                'organization' => 'Test Org',
                'phone' => '+123321123',
                'postalCode' => '14608',
                'primary' => true,
                'regionText' => null,
                'street' => '1215 Caldwell Road',
                'street2' => 'street2',
                'country' => [
                    'iso2Code' => 'US'
                ],
                'frontendOwner' => [
                    'id' => 42,
                    'name' => 'customer.level_1'
                ],
                'owner' => [
                    'username' => 'admin'
                ],
                'region' => [
                    'combinedCode' => 'US-NY'
                ],
                'Billing' => true,
                'Default Billing' => false,
                'Shipping' => true,
                'Default Shipping' => true,
            ],
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
