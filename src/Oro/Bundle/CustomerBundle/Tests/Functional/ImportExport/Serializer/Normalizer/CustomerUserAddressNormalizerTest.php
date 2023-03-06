<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport\Serializer\Normalizer;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\ImportExport\Serializer\Normalizer\CustomerUserAddressNormalizer;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadFullCustomerUserAddress;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerUserAddressNormalizerTest extends WebTestCase
{
    private CustomerUserAddressNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadFullCustomerUserAddress::class]);

        $this->normalizer = $this->getContainer()->get('oro_customer.importexport.normalizer.customer_user_address');
        $this->normalizer->setSerializer($this->getContainer()->get('oro_importexport.serializer'));
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupportsNormalization(string|object $data, bool $expected)
    {
        if (is_string($data)) {
            $data = $this->getReference($data);
        }
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data));
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupportsDenormalization(string|object $data, bool $expected)
    {
        if (is_string($data)) {
            $data = $this->getReference($data);
        }
        $this->assertSame($expected, $this->normalizer->supportsDenormalization([], get_class($data)));
    }

    public function supportsDataProvider(): array
    {
        return [
            ['customer_user.address_1_full', true],
            [new \stdClass(), false]
        ];
    }

    public function testNormalize()
    {
        /** @var CustomerUserAddress $address */
        $address = $this->getReference('customer_user.address_1_full');
        $data = $this->normalizer->normalize($address);
        $this->assertEquals(
            [
                'city' => 'Rochester',
                'firstName' => 'First',
                'id' => $address->getId(),
                'label' => 'customer_user.address_1_full',
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
                    'email' => 'customer_user@example.com'
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
        /** @var CustomerUserAddress $address */
        $address = $this->normalizer->denormalize(
            [
                'city' => 'Rochester',
                'firstName' => 'First',
                'id' => 11,
                'label' => 'customer_user.address_1_full',
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
                    'email' => 'customer_user@example.com'
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
            CustomerUserAddress::class
        );

        $this->assertInstanceOf(CustomerUserAddress::class, $address);
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
        $this->assertEquals('customer_user.address_1_full', $address->getLabel());
        $this->assertEquals('1215 Caldwell Road', $address->getStreet());
        $this->assertEquals('Rochester', $address->getCity());
        $this->assertEquals('14608', $address->getPostalCode());

        $this->assertEquals('US', $address->getCountry()->getIso2Code());
        $this->assertEquals('US-NY', $address->getRegion()->getCombinedCode());
        $this->assertEquals(42, $address->getFrontendOwner()->getId());
        $this->assertEquals('customer_user@example.com', $address->getFrontendOwner()->getEmail());
        $this->assertEquals('admin', $address->getOwner()->getUsername());

        $this->assertTrue($address->hasTypeWithName('billing'));
        $this->assertTrue($address->hasTypeWithName('shipping'));
        $this->assertFalse($address->hasDefault('billing'));
        $this->assertTrue($address->hasDefault('shipping'));
    }
}
