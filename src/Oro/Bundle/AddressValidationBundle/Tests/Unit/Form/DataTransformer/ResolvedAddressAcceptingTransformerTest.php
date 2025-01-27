<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Tests\Unit\Form\DataTransformer;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressValidationBundle\Form\DataTransformer\ResolvedAddressAcceptingTransformer;
use Oro\Bundle\AddressValidationBundle\Model\ResolvedAddress;
use Oro\Bundle\AddressValidationBundle\ResolvedAddress\ResolvedAddressAcceptorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ResolvedAddressAcceptingTransformerTest extends TestCase
{
    private ResolvedAddressAcceptorInterface&MockObject $resolvedAddressAcceptor;

    private ResolvedAddressAcceptingTransformer $transformer;

    protected function setUp(): void
    {
        $this->resolvedAddressAcceptor = $this->createMock(ResolvedAddressAcceptorInterface::class);
        $this->transformer = new ResolvedAddressAcceptingTransformer($this->resolvedAddressAcceptor);
    }

    public function testTransform(): void
    {
        $address = $this->createMock(AbstractAddress::class);

        self::assertSame($address, $this->transformer->transform($address));
    }

    public function testReverseTransformWithNull(): void
    {
        self::assertNull($this->transformer->reverseTransform(null));
    }

    public function testReverseTransformWithNonResolvedAddress(): void
    {
        $address = $this->createMock(AbstractAddress::class);

        self::assertSame($address, $this->transformer->reverseTransform($address));
    }

    public function testReverseTransformWithResolvedAddressAndNonAddressBookAware(): void
    {
        $originalAddress = $this->createMock(AbstractAddress::class);
        $resolvedAddress = new ResolvedAddress($originalAddress);
        $acceptedAddress = $this->createMock(AbstractAddress::class);

        $this->resolvedAddressAcceptor
            ->expects(self::once())
            ->method('acceptResolvedAddress')
            ->with($resolvedAddress)
            ->willReturn($acceptedAddress);

        $result = $this->transformer->reverseTransform($resolvedAddress);

        self::assertSame($acceptedAddress, $result);
    }
}
