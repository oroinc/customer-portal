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

        self::assertEquals(
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

        self::assertEquals(
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

        self::assertInstanceOf(CustomerUserAddress::class, $address);
        self::assertEquals(11, $address->getId());

        self::assertEquals('Test Org', $address->getOrganization());
        self::assertEquals('Mr', $address->getNamePrefix());
        self::assertEquals('First', $address->getFirstName());
        self::assertEquals('Middle', $address->getMiddleName());
        self::assertEquals('Last', $address->getLastName());
        self::assertEquals('Sf', $address->getNameSuffix());
        self::assertEquals('street2', $address->getStreet2());
        self::assertEquals('+123321123', $address->getPhone());
        self::assertTrue($address->isPrimary());
        self::assertEquals('customer_user.address_1_full', $address->getLabel());
        self::assertEquals('1215 Caldwell Road', $address->getStreet());
        self::assertEquals('Rochester', $address->getCity());
        self::assertEquals('14608', $address->getPostalCode());

        self::assertEquals('US', $address->getCountry()->getIso2Code());
        self::assertEquals('US-NY', $address->getRegion()->getCombinedCode());
        self::assertEquals(42, $address->getFrontendOwner()->getId());
        self::assertEquals('customer_user@example.com', $address->getFrontendOwner()->getEmail());
        self::assertEquals('admin', $address->getOwner()->getUserIdentifier());
        self::assertInstanceOf(\DateTime::class, $address->getValidatedAt());

        self::assertTrue($address->hasTypeWithName('billing'));
        self::assertTrue($address->hasTypeWithName('shipping'));
        self::assertFalse($address->hasDefault('billing'));
        self::assertTrue($address->hasDefault('shipping'));
    }

    public function testDenormalizeValidatedAt(): void
    {
        $address = $this->normalizer->denormalize(['validatedAt' => true], CustomerUserAddress::class);
        self::assertInstanceOf(\DateTime::class, $address->getValidatedAt());

        $address = $this->normalizer->denormalize(['validatedAt' => 'true'], CustomerUserAddress::class);
        self::assertInstanceOf(\DateTime::class, $address->getValidatedAt());


        $address = $this->normalizer->denormalize(['validatedAt' => 'TruE'], CustomerUserAddress::class);
        self::assertInstanceOf(\DateTime::class, $address->getValidatedAt());

        $address = $this->normalizer->denormalize(['validatedAt' => 1], CustomerUserAddress::class);
        self::assertInstanceOf(\DateTime::class, $address->getValidatedAt());

        $address = $this->normalizer->denormalize(['validatedAt' => '1'], CustomerUserAddress::class);
        self::assertInstanceOf(\DateTime::class, $address->getValidatedAt());

        $address = $this->normalizer->denormalize(['validatedAt' => 'yes'], CustomerUserAddress::class);
        self::assertInstanceOf(\DateTime::class, $address->getValidatedAt());

        $address = $this->normalizer->denormalize(['validatedAt' => 'YeS'], CustomerUserAddress::class);
        self::assertInstanceOf(\DateTime::class, $address->getValidatedAt());

        $address = $this->normalizer->denormalize(['validatedAt' => false], CustomerUserAddress::class);
        self::assertEquals(null, $address->getValidatedAt());

        $address = $this->normalizer->denormalize(['validatedAt' => 'false'], CustomerUserAddress::class);
        self::assertEquals(null, $address->getValidatedAt());

        $address = $this->normalizer->denormalize(['validatedAt' => 'FaLse'], CustomerUserAddress::class);
        self::assertEquals(null, $address->getValidatedAt());

        $address = $this->normalizer->denormalize(['validatedAt' => 0], CustomerUserAddress::class);
        self::assertEquals(null, $address->getValidatedAt());

        $address = $this->normalizer->denormalize(['validatedAt' => '0'], CustomerUserAddress::class);
        self::assertEquals(null, $address->getValidatedAt());

        $address = $this->normalizer->denormalize(['validatedAt' => 'no'], CustomerUserAddress::class);
        self::assertEquals(null, $address->getValidatedAt());

        $address = $this->normalizer->denormalize(['validatedAt' => 'No'], CustomerUserAddress::class);
        self::assertEquals(null, $address->getValidatedAt());
    }
}
