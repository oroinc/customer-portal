<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Utils;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerAddress;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserAddress;
use Oro\Bundle\CustomerBundle\Tests\Unit\Stub\AddressBookAwareAddressStub;
use Oro\Bundle\CustomerBundle\Utils\AddressCopier;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class AddressCopierTest extends TestCase
{
    private PropertyAccessorInterface|MockObject $propertyAccessor;

    private AddressCopier $addressCopier;

    private EntityManager|MockObject $entityManager;

    protected function setUp(): void
    {
        $doctrine = $this->createMock(ManagerRegistry::class);
        $this->propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        $this->addressCopier = new AddressCopier($doctrine, $this->propertyAccessor);

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $doctrine->method('getManagerForClass')->willReturn($this->entityManager);
    }

    public function testCopyToAddressWithFieldsAndAssociations(): void
    {
        $fromAddress = new CustomerAddress();
        $toAddress = new CustomerUserAddress();

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata
            ->method('getFieldNames')
            ->willReturn(['field1', 'field2']);
        $metadata
            ->method('getAssociationNames')
            ->willReturn(['association1', 'association2']);

        $this->entityManager->method('getClassMetadata')->willReturn($metadata);

        $this->propertyAccessor
            ->expects(self::exactly(4))
            ->method('getValue')
            ->willReturnMap([
                [$fromAddress, 'field1', 'value1'],
                [$fromAddress, 'field2', 'value2'],
                [$fromAddress, 'association1', 'value3'],
                [$fromAddress, 'association2', 'value4'],
            ]);

        $this->propertyAccessor->expects(self::exactly(4))
            ->method('setValue')
            ->withConsecutive(
                [$toAddress, 'field1', 'value1'],
                [$toAddress, 'field2', 'value2'],
                [$toAddress, 'association1', 'value3'],
                [$toAddress, 'association2', 'value4']
            );

        $this->addressCopier->copyToAddress($fromAddress, $toAddress);
    }

    public function testCopyToAddressWithCustomerAddressAwareInterface(): void
    {
        $fromAddress = new CustomerAddress();
        $toAddress = new AddressBookAwareAddressStub();

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata
            ->method('getFieldNames')
            ->willReturn([]);

        $metadata
            ->method('getAssociationNames')
            ->willReturn([]);

        $this->entityManager->method('getClassMetadata')->willReturn($metadata);

        $this->addressCopier->copyToAddress($fromAddress, $toAddress);

        self::assertSame($fromAddress, $toAddress->getCustomerAddress());
    }

    public function testCopyToAddressWithCustomerUserAddressAwareInterface(): void
    {
        $fromAddress = new CustomerUserAddress();
        $toAddress = new AddressBookAwareAddressStub();

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata
            ->method('getFieldNames')
            ->willReturn([]);

        $metadata
            ->method('getAssociationNames')
            ->willReturn([]);

        $this->entityManager->method('getClassMetadata')->willReturn($metadata);

        $this->addressCopier->copyToAddress($fromAddress, $toAddress);

        self::assertSame($fromAddress, $toAddress->getCustomerUserAddress());
    }

    public function testSetValueWhenEmptyCollection(): void
    {
        $fromAddress = $this->createMock(AbstractAddress::class);
        $toAddress = $this->createMock(AbstractAddress::class);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata
            ->method('getFieldNames')
            ->willReturn([]);

        $metadata
            ->method('getAssociationNames')
            ->willReturn(['emptyCollection']);

        $emptyCollection = $this->createMock(Collection::class);
        $emptyCollection
            ->method('isEmpty')
            ->willReturn(true);

        $this->entityManager->method('getClassMetadata')->willReturn($metadata);

        $this->propertyAccessor->expects(self::once())
            ->method('getValue')
            ->with($fromAddress, 'emptyCollection')
            ->willReturn($emptyCollection);

        $this->propertyAccessor->expects(self::once())
            ->method('setValue')
            ->with($toAddress, 'emptyCollection', $emptyCollection);

        $this->addressCopier->copyToAddress($fromAddress, $toAddress);
    }

    public function testSetValueWhenEmptyField(): void
    {
        $fromAddress = $this->createMock(AbstractAddress::class);
        $toAddress = $this->createMock(AbstractAddress::class);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata
            ->method('getFieldNames')
            ->willReturn(['emptyField']);

        $metadata
            ->method('getAssociationNames')
            ->willReturn([]);

        $this->entityManager->method('getClassMetadata')->willReturn($metadata);

        $this->propertyAccessor->expects(self::once())
            ->method('getValue')
            ->with($fromAddress, 'emptyField')
            ->willReturn(null);

        $this->propertyAccessor->expects(self::once())
            ->method('setValue')
            ->with($toAddress, 'emptyField', null);

        $this->addressCopier->copyToAddress($fromAddress, $toAddress);
    }

    public function testIgnoresSkippedFields(): void
    {
        $fromAddress = $this->createMock(AbstractAddress::class);
        $toAddress = $this->createMock(AbstractAddress::class);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata
            ->method('getFieldNames')
            ->willReturn(['id', 'created', 'updated', 'regularField']);

        $metadata
            ->method('getAssociationNames')
            ->willReturn([]);

        $this->entityManager->method('getClassMetadata')->willReturn($metadata);

        $regularFieldValue = 'sample_value';
        $this->propertyAccessor->expects(self::once())
            ->method('getValue')
            ->with($fromAddress, 'regularField')
            ->willReturn($regularFieldValue);

        $this->propertyAccessor->expects(self::once())
            ->method('setValue')
            ->with($toAddress, 'regularField', $regularFieldValue);

        $this->addressCopier->copyToAddress($fromAddress, $toAddress);
    }

    public function testSetValueHandlesNoSuchPropertyException(): void
    {
        $fromAddress = $this->createMock(AbstractAddress::class);
        $toAddress = $this->createMock(AbstractAddress::class);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata
            ->method('getFieldNames')
            ->willReturn(['invalidField']);

        $metadata
            ->method('getAssociationNames')
            ->willReturn([]);

        $this->entityManager->method('getClassMetadata')->willReturn($metadata);

        $this->propertyAccessor->expects(self::once())
            ->method('getValue')
            ->with($fromAddress, 'invalidField')
            ->willThrowException(new NoSuchPropertyException());

        $this->propertyAccessor->expects(self::never())
            ->method('setValue');

        $this->addressCopier->copyToAddress($fromAddress, $toAddress);
    }
}
