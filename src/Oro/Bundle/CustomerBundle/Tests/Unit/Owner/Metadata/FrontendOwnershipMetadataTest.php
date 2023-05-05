<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Owner\Metadata;

use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadata;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;

class FrontendOwnershipMetadataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider frontendOwnerTypeException
     */
    public function testSetFrontendOwner(array $ownerType, int $expectedOwnerType, array $exceptionDefinition = [])
    {
        if ($exceptionDefinition) {
            [$exception, $message] = $exceptionDefinition;
            $this->expectException($exception);
            $this->expectExceptionMessage($message);
        }

        [$frontendOwnerType, $frontendOwnerFieldName, $frontendOwnerColumnName] = $ownerType;
        $metadata = new FrontendOwnershipMetadata(
            $frontendOwnerType,
            $frontendOwnerFieldName,
            $frontendOwnerColumnName
        );

        $this->assertEquals($expectedOwnerType, $metadata->getOwnerType());
        $this->assertEquals($frontendOwnerFieldName, $metadata->getOwnerFieldName());
        $this->assertEquals($frontendOwnerColumnName, $metadata->getOwnerColumnName());
    }

    public function frontendOwnerTypeException(): array
    {
        return [
            [
                ['FRONTEND_USER', 'customer_user', 'customer_user_id'],
                FrontendOwnershipMetadata::OWNER_TYPE_FRONTEND_USER,
            ],
            [
                ['FRONTEND_CUSTOMER', 'FRONTEND_CUSTOMER', 'customer_id'],
                FrontendOwnershipMetadata::OWNER_TYPE_FRONTEND_CUSTOMER,
            ],
            [
                ['UNKNOWN', 'FRONTEND_CUSTOMER', 'customer_id'],
                FrontendOwnershipMetadata::OWNER_TYPE_FRONTEND_CUSTOMER,
                [
                    \InvalidArgumentException::class,
                    'Unknown owner type: UNKNOWN.',
                ],
            ],
            [
                ['UNKNOWN', 'FRONTEND_CUSTOMER', 'customer_id'],
                FrontendOwnershipMetadata::OWNER_TYPE_FRONTEND_CUSTOMER,
                [
                    \InvalidArgumentException::class,
                    'Unknown owner type: UNKNOWN.',
                ],
            ],
            [
                ['', '', ''],
                FrontendOwnershipMetadata::OWNER_TYPE_NONE,
            ],
            [
                ['FRONTEND_CUSTOMER', '', 'customer_id'],
                FrontendOwnershipMetadata::OWNER_TYPE_FRONTEND_CUSTOMER,
                [
                    \InvalidArgumentException::class,
                    'The owner field name must not be empty.',
                ],
            ],
            [
                ['FRONTEND_CUSTOMER', 'FRONTEND_CUSTOMER', ''],
                FrontendOwnershipMetadata::OWNER_TYPE_FRONTEND_CUSTOMER,
                [
                    \InvalidArgumentException::class,
                    'The owner column name must not be empty.',
                ],
            ],
        ];
    }

    public function testIsUserOwned()
    {
        $metadata = new FrontendOwnershipMetadata();
        $this->assertFalse($metadata->isUserOwned());

        $metadata = new FrontendOwnershipMetadata('FRONTEND_USER', 'customer_user', 'customer_user_id');
        $this->assertTrue($metadata->isUserOwned());

        $metadata = new FrontendOwnershipMetadata('FRONTEND_CUSTOMER', 'FRONTEND_CUSTOMER', 'customer_id');
        $this->assertFalse($metadata->isUserOwned());
    }

    public function testIsBusinessUnitOwned()
    {
        $metadata = new FrontendOwnershipMetadata();
        $this->assertFalse($metadata->isBusinessUnitOwned());

        $metadata = new FrontendOwnershipMetadata('FRONTEND_CUSTOMER', 'FRONTEND_CUSTOMER', 'customer_id');
        $this->assertTrue($metadata->isBusinessUnitOwned());

        $metadata = new FrontendOwnershipMetadata('FRONTEND_USER', 'customer_user', 'customer_user_id');
        $this->assertFalse($metadata->isBusinessUnitOwned());
    }

    public function testSerialization()
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
        $data = serialize($metadata);

        $metadata = new FrontendOwnershipMetadata();
        $this->assertFalse($metadata->isUserOwned());
        $this->assertFalse($metadata->isBusinessUnitOwned());
        $this->assertEquals('', $metadata->getOwnerFieldName());
        $this->assertEquals('', $metadata->getOwnerColumnName());

        $metadata = unserialize($data);
        $this->assertTrue($metadata->isUserOwned());
        $this->assertFalse($metadata->isBusinessUnitOwned());
        $this->assertEquals('customerUser', $metadata->getOwnerFieldName());
        $this->assertEquals('customer_user_id', $metadata->getOwnerColumnName());
        $this->assertEquals('organization', $metadata->getOrganizationFieldName());
        $this->assertEquals('organization_id', $metadata->getOrganizationColumnName());
        $this->assertEquals('customer', $metadata->getCustomerFieldName());
        $this->assertEquals('customer_id', $metadata->getCustomerColumnName());
    }

    public function testIsOrganizationOwned()
    {
        $metadata = new FrontendOwnershipMetadata();
        $this->assertFalse($metadata->isOrganizationOwned());
    }

    /**
     * @dataProvider getAccessLevelNamesDataProvider
     */
    public function testGetAccessLevelNames(array $arguments, array $levels)
    {
        $reflection = new \ReflectionClass(FrontendOwnershipMetadata::class);
        /** @var FrontendOwnershipMetadata $metadata */
        $metadata = $reflection->newInstanceArgs($arguments);
        $this->assertEquals($levels, $metadata->getAccessLevelNames());
    }

    public function getAccessLevelNamesDataProvider(): array
    {
        return [
            'no owner' => [
                'arguments' => [],
                'levels' => [
                    0 => AccessLevel::NONE_LEVEL_NAME,
                    5 => AccessLevel::getAccessLevelName(5),
                ],
            ],
            'basic level owned' => [
                'arguments' => ['FRONTEND_USER', 'owner', 'owner_id'],
                'levels' => [
                    0 => AccessLevel::NONE_LEVEL_NAME,
                    1 => AccessLevel::getAccessLevelName(1),
                    2 => AccessLevel::getAccessLevelName(2),
                    3 => AccessLevel::getAccessLevelName(3),
                ],
            ],
            'local level owned' => [
                'arguments' => ['FRONTEND_CUSTOMER', 'owner', 'owner_id'],
                'levels' => [
                    0 => AccessLevel::NONE_LEVEL_NAME,
                    2 => AccessLevel::getAccessLevelName(2),
                    3 => AccessLevel::getAccessLevelName(3),
                ],
            ],
        ];
    }

    public function testGetAccessLevelNamesInvalidOwner()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Owner type 1 is not supported');

        $metadata = new FrontendOwnershipMetadata('ORGANIZATION', 'owner', 'owner_id');
        $metadata->getAccessLevelNames();
    }
}
