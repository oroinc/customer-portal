<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Owner\Metadata;

use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadata;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class FrontendOwnershipMetadataTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructWithoutParameters(): void
    {
        $metadata = new FrontendOwnershipMetadata();
        self::assertEquals(FrontendOwnershipMetadata::OWNER_TYPE_NONE, $metadata->getOwnerType());
        self::assertFalse($metadata->hasOwner());
        self::assertFalse($metadata->isOrganizationOwned());
        self::assertFalse($metadata->isBusinessUnitOwned());
        self::assertFalse($metadata->isUserOwned());
        self::assertSame('', $metadata->getOrganizationFieldName());
        self::assertSame('', $metadata->getOrganizationColumnName());
        self::assertSame('', $metadata->getOwnerFieldName());
        self::assertSame('', $metadata->getOwnerColumnName());
        self::assertSame('', $metadata->getCustomerFieldName());
        self::assertSame('', $metadata->getCustomerColumnName());
    }

    public function testConstructWithNoneOwnership(): void
    {
        $metadata = new FrontendOwnershipMetadata('NONE');
        self::assertEquals(FrontendOwnershipMetadata::OWNER_TYPE_NONE, $metadata->getOwnerType());
        self::assertFalse($metadata->hasOwner());
        self::assertFalse($metadata->isOrganizationOwned());
        self::assertFalse($metadata->isBusinessUnitOwned());
        self::assertFalse($metadata->isUserOwned());
        self::assertSame('', $metadata->getOrganizationFieldName());
        self::assertSame('', $metadata->getOrganizationColumnName());
        self::assertSame('', $metadata->getOwnerFieldName());
        self::assertSame('', $metadata->getOwnerColumnName());
        self::assertSame('', $metadata->getCustomerFieldName());
        self::assertSame('', $metadata->getCustomerColumnName());
    }

    public function testConstructWithCustomerOwnership(): void
    {
        $metadata = new FrontendOwnershipMetadata('FRONTEND_CUSTOMER', 'customer', 'customer_id');
        self::assertEquals(FrontendOwnershipMetadata::OWNER_TYPE_FRONTEND_CUSTOMER, $metadata->getOwnerType());
        self::assertTrue($metadata->hasOwner());
        self::assertFalse($metadata->isOrganizationOwned());
        self::assertTrue($metadata->isBusinessUnitOwned());
        self::assertFalse($metadata->isUserOwned());
        self::assertSame('', $metadata->getOrganizationFieldName());
        self::assertSame('', $metadata->getOrganizationColumnName());
        self::assertSame('customer', $metadata->getOwnerFieldName());
        self::assertSame('customer_id', $metadata->getOwnerColumnName());
        self::assertSame('', $metadata->getCustomerFieldName());
        self::assertSame('', $metadata->getCustomerColumnName());
    }

    public function testConstructWithCustomerUserOwnership(): void
    {
        $metadata = new FrontendOwnershipMetadata('FRONTEND_USER', 'customerUser', 'customer_user_id');
        self::assertEquals(FrontendOwnershipMetadata::OWNER_TYPE_FRONTEND_USER, $metadata->getOwnerType());
        self::assertTrue($metadata->hasOwner());
        self::assertFalse($metadata->isOrganizationOwned());
        self::assertFalse($metadata->isBusinessUnitOwned());
        self::assertTrue($metadata->isUserOwned());
        self::assertSame('', $metadata->getOrganizationFieldName());
        self::assertSame('', $metadata->getOrganizationColumnName());
        self::assertSame('customerUser', $metadata->getOwnerFieldName());
        self::assertSame('customer_user_id', $metadata->getOwnerColumnName());
        self::assertSame('', $metadata->getCustomerFieldName());
        self::assertSame('', $metadata->getCustomerColumnName());
    }

    public function testConstructWithInvalidOwnerType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown owner type: test.');

        new FrontendOwnershipMetadata('test');
    }

    public function testConstructCustomerrOwnershipWithoutOwnerFieldName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The owner field name must not be empty.');

        new FrontendOwnershipMetadata('FRONTEND_USER');
    }

    public function testConstructCustomerOwnershipWithoutOwnerIdColumnName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The owner column name must not be empty.');

        new FrontendOwnershipMetadata('FRONTEND_USER', 'customerUser');
    }

    public function testConstructCustomerUserOwnershipWithoutOwnerFieldName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The owner field name must not be empty.');

        new FrontendOwnershipMetadata('FRONTEND_CUSTOMER');
    }

    public function testConstructCustomerUserOwnershipWithoutOwnerIdColumnName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The owner column name must not be empty.');

        new FrontendOwnershipMetadata('FRONTEND_CUSTOMER', 'customer');
    }

    /**
     * @dataProvider constructWithUnsupportedOwnerTypeDataProvider
     */
    public function testConstructWithUnsupportedOwnerType(string $ownerType): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Unsupported owner type: %s.', $ownerType));

        new FrontendOwnershipMetadata($ownerType);
    }

    public function constructWithUnsupportedOwnerTypeDataProvider(): array
    {
        return [
            ['USER'],
            ['BUSINESS_UNIT'],
            ['ORGANIZATION']
        ];
    }

    public function testSerialization(): void
    {
        $metadata = new FrontendOwnershipMetadata(
            'FRONTEND_USER',
            'customerUser',
            'customer_user_id',
            'organization',
            'organization_id',
            'customer',
            'customer_id'
        );

        $unserializedMetadata = unserialize(serialize($metadata));

        self::assertEquals($metadata, $unserializedMetadata);
        self::assertNotSame($metadata, $unserializedMetadata);
    }

    public function testSetState(): void
    {
        $metadata = new FrontendOwnershipMetadata(
            'FRONTEND_USER',
            'customerUser',
            'customer_user_id',
            'organization',
            'organization_id',
            'customer',
            'customer_id'
        );

        $restoredMetadata = FrontendOwnershipMetadata::__set_state(
            [
                'ownerType' => $metadata->getOwnerType(),
                'organizationFieldName' => $metadata->getOrganizationFieldName(),
                'organizationColumnName' => $metadata->getOrganizationColumnName(),
                'ownerFieldName' => $metadata->getOwnerFieldName(),
                'ownerColumnName' => $metadata->getOwnerColumnName(),
                'customerFieldName' => $metadata->getCustomerFieldName(),
                'customerColumnName' => $metadata->getCustomerColumnName(),
                'not_exists' => true
            ]
        );

        self::assertEquals($metadata, $restoredMetadata);
        self::assertNotSame($metadata, $restoredMetadata);
    }

    /**
     * @dataProvider getAccessLevelNamesDataProvider
     */
    public function testGetAccessLevelNames(array $params, array $levels): void
    {
        [$ownerType, $ownerFieldName, $ownerColumnName] = $params;
        $metadata = new FrontendOwnershipMetadata($ownerType, $ownerFieldName, $ownerColumnName);

        self::assertEquals($levels, $metadata->getAccessLevelNames());
    }

    public function getAccessLevelNamesDataProvider(): array
    {
        return [
            'no owner' => [
                ['NONE', '', ''],
                [
                    AccessLevel::NONE_LEVEL => AccessLevel::NONE_LEVEL_NAME,
                    AccessLevel::SYSTEM_LEVEL => AccessLevel::getAccessLevelName(AccessLevel::SYSTEM_LEVEL)
                ]
            ],
            'basic level owned' => [
                ['FRONTEND_USER', 'owner', 'owner_id'],
                [
                    AccessLevel::NONE_LEVEL => AccessLevel::NONE_LEVEL_NAME,
                    AccessLevel::BASIC_LEVEL => AccessLevel::getAccessLevelName(AccessLevel::BASIC_LEVEL),
                    AccessLevel::LOCAL_LEVEL => AccessLevel::getAccessLevelName(AccessLevel::LOCAL_LEVEL),
                    AccessLevel::DEEP_LEVEL => AccessLevel::getAccessLevelName(AccessLevel::DEEP_LEVEL)
                ]
            ],
            'local level owned' => [
                ['FRONTEND_CUSTOMER', 'owner', 'owner_id'],
                [
                    AccessLevel::NONE_LEVEL => AccessLevel::NONE_LEVEL_NAME,
                    AccessLevel::LOCAL_LEVEL => AccessLevel::getAccessLevelName(AccessLevel::LOCAL_LEVEL),
                    AccessLevel::DEEP_LEVEL => AccessLevel::getAccessLevelName(AccessLevel::DEEP_LEVEL)
                ]
            ]
        ];
    }
}
