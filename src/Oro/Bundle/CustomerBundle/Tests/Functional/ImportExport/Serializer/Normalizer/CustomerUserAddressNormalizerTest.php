<?php

namespace Oro\Bundle\CustomerBundle\Tests\Functional\ImportExport\Serializer\Normalizer;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Tests\Functional\DataFixtures\LoadFullCustomerUserAddress;

class CustomerUserAddressNormalizerTest extends AbstractCustomerAddressNormalizerTest
{
    #[\Override]
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadFullCustomerUserAddress::class]);

        $this->normalizer = $this->getContainer()->get('oro_customer.importexport.normalizer.customer_user_address');
        $this->normalizer->setSerializer($this->getContainer()->get('oro_importexport.serializer'));
    }

    #[\Override]
    public function supportsDataProvider(): array
    {
        return [
            ['customer_user.address_1_full', true],
            [new \stdClass(), false]
        ];
    }

    public function testNormalizeForTheCustomerUserEntity(): void
    {
        /** @var CustomerUserAddress $address */
        $address = $this->getReference('customer_user.address_1_full');
        $data = $this->normalizer->normalize($address, context: ['entityName' => CustomerUser::class]);

        $this->assertEquals(
            array_merge([
                'id' => $address->getId(),
                'label' => 'customer_user.address_1_full',
                'frontendOwner' => [
                    'email' => 'customer_user@example.com'
                ],
            ], self::getColumnsForNormalizer()),
            $data
        );
    }

    public function testNormalizeForTheCustomerUserAddressEntity(): void
    {
        /** @var CustomerUserAddress $address */
        $address = $this->getReference('customer_user.address_1_full');
        $data = $this->normalizer->normalize($address, context: ['entityName' => CustomerUserAddress::class]);

        $this->assertEquals(
            array_merge([
                'id' => $address->getId(),
                'label' => 'customer_user.address_1_full',
                'frontendOwner' => [
                    'id' => $address->getFrontendOwner()->getId(),
                    'email' => 'customer_user@example.com'
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
        $now = (new \DateTime('now', new \DateTimeZone('UTC')))->format(\DateTimeInterface::ATOM);

        /** @var CustomerUserAddress $address */
        $address = $this->normalizer->denormalize(
            array_merge(
                self::getColumnsForNormalizer(),
                [
                    'id' => 11,
                    'label' => 'customer_user.address_1_full',
                    'frontendOwner' => [
                        'id' => 42,
                        'email' => 'customer_user@example.com'
                    ],
                    'Billing' => true,
                    'Default Billing' => false,
                    'Shipping' => true,
                    'Default Shipping' => true,
                    'validatedAt' => $now,
                ]
            ),
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
        $this->assertEquals('admin', $address->getOwner()->getUserIdentifier());
        $this->assertEquals($now, $address->getValidatedAt()->format(\DateTimeInterface::ATOM));

        $this->assertTrue($address->hasTypeWithName('billing'));
        $this->assertTrue($address->hasTypeWithName('shipping'));
        $this->assertFalse($address->hasDefault('billing'));
        $this->assertTrue($address->hasDefault('shipping'));
    }

    public function testDenormalizeValidatedAt(): void
    {
        $now = (new \DateTime('now', new \DateTimeZone('UTC')))->format(\DateTimeInterface::ATOM);
        $address = $this->normalizer->denormalize(['validatedAt' => true], CustomerUserAddress::class);
        $this->assertEquals($now, $address->getValidatedAt()->format(\DateTimeInterface::ATOM));

        $address = $this->normalizer->denormalize(['validatedAt' => 'true'], CustomerUserAddress::class);
        $this->assertEquals($now, $address->getValidatedAt()->format(\DateTimeInterface::ATOM));

        $address = $this->normalizer->denormalize(['validatedAt' => 'TruE'], CustomerUserAddress::class);
        $this->assertEquals($now, $address->getValidatedAt()->format(\DateTimeInterface::ATOM));

        $address = $this->normalizer->denormalize(['validatedAt' => 1], CustomerUserAddress::class);
        $this->assertEquals($now, $address->getValidatedAt()->format(\DateTimeInterface::ATOM));

        $address = $this->normalizer->denormalize(['validatedAt' => '1'], CustomerUserAddress::class);
        $this->assertEquals($now, $address->getValidatedAt()->format(\DateTimeInterface::ATOM));

        $address = $this->normalizer->denormalize(['validatedAt' => 'yes'], CustomerUserAddress::class);
        $this->assertEquals($now, $address->getValidatedAt()->format(\DateTimeInterface::ATOM));

        $address = $this->normalizer->denormalize(['validatedAt' => 'YeS'], CustomerUserAddress::class);
        $this->assertEquals($now, $address->getValidatedAt()->format(\DateTimeInterface::ATOM));

        $address = $this->normalizer->denormalize(['validatedAt' => false], CustomerUserAddress::class);
        $this->assertEquals(null, $address->getValidatedAt());

        $address = $this->normalizer->denormalize(['validatedAt' => 'false'], CustomerUserAddress::class);
        $this->assertEquals(null, $address->getValidatedAt());

        $address = $this->normalizer->denormalize(['validatedAt' => 'FaLse'], CustomerUserAddress::class);
        $this->assertEquals(null, $address->getValidatedAt());

        $address = $this->normalizer->denormalize(['validatedAt' => 0], CustomerUserAddress::class);
        $this->assertEquals(null, $address->getValidatedAt());

        $address = $this->normalizer->denormalize(['validatedAt' => '0'], CustomerUserAddress::class);
        $this->assertEquals(null, $address->getValidatedAt());

        $address = $this->normalizer->denormalize(['validatedAt' => 'no'], CustomerUserAddress::class);
        $this->assertEquals(null, $address->getValidatedAt());

        $address = $this->normalizer->denormalize(['validatedAt' => 'No'], CustomerUserAddress::class);
        $this->assertEquals(null, $address->getValidatedAt());
    }
}
