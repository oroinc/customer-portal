<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Tests\Unit\Resolver\Factory;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\AddressValidationBundle\Exception\TransportNotSupportedException;
use Oro\Bundle\AddressValidationBundle\Resolver\AddressValidationResolverInterface;
use Oro\Bundle\AddressValidationBundle\Resolver\Factory\AddressValidationResolverChainFactory;
use Oro\Bundle\AddressValidationBundle\Resolver\Factory\AddressValidationResolverFactoryInterface;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AddressValidationResolverChainFactoryTest extends TestCase
{
    private AddressValidationResolverFactoryInterface&MockObject $innerFactory1;
    private AddressValidationResolverFactoryInterface&MockObject $innerFactory2;
    private AddressValidationResolverChainFactory $factory;

    #[\Override]
    protected function setUp(): void
    {
        $this->innerFactory1 = $this->createMock(AddressValidationResolverFactoryInterface::class);
        $this->innerFactory2 = $this->createMock(AddressValidationResolverFactoryInterface::class);

        $this->factory = new AddressValidationResolverChainFactory([
            $this->innerFactory1,
            $this->innerFactory2,
        ]);
    }

    public function testCreateForTransportSupportedByFirstFactory(): void
    {
        $transport = $this->createMock(Transport::class);
        $resolver = $this->createMock(AddressValidationResolverInterface::class);

        $this->innerFactory1->expects(self::once())
            ->method('isSupported')
            ->with($transport)
            ->willReturn(true);

        $this->innerFactory1->expects(self::once())
            ->method('createForTransport')
            ->with($transport)
            ->willReturn($resolver);

        $this->innerFactory2->expects(self::never())
            ->method('isSupported');

        self::assertSame($resolver, $this->factory->createForTransport($transport));
    }

    public function testCreateForTransportSupportedBySecondFactory(): void
    {
        $transport = $this->createMock(Transport::class);
        $resolver = $this->createMock(AddressValidationResolverInterface::class);

        $this->innerFactory1->expects(self::once())
            ->method('isSupported')
            ->with($transport)
            ->willReturn(false);

        $this->innerFactory2->expects(self::once())
            ->method('isSupported')
            ->with($transport)
            ->willReturn(true);

        $this->innerFactory2->expects(self::once())
            ->method('createForTransport')
            ->with($transport)
            ->willReturn($resolver);

        self::assertSame($resolver, $this->factory->createForTransport($transport));
    }

    public function testCreateForTransportThrowsExceptionWhenNoFactorySupportsTransport(): void
    {
        $transport = $this->createMock(Transport::class);

        $this->innerFactory1->expects(self::once())
            ->method('isSupported')
            ->with($transport)
            ->willReturn(false);

        $this->innerFactory2->expects(self::once())
            ->method('isSupported')
            ->with($transport)
            ->willReturn(false);

        $this->expectException(TransportNotSupportedException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Transport %s #%s is not supported by any %s',
                ClassUtils::getClass($transport),
                $transport->getId(),
                AddressValidationResolverFactoryInterface::class
            )
        );

        $this->factory->createForTransport($transport);
    }

    public function testIsSupportedReturnsTrueWhenSupportedByFirstFactory(): void
    {
        $transport = $this->createMock(Transport::class);

        $this->innerFactory1->expects(self::once())
            ->method('isSupported')
            ->with($transport)
            ->willReturn(true);

        $this->innerFactory2->expects(self::never())
            ->method('isSupported');

        self::assertTrue($this->factory->isSupported($transport));
    }

    public function testIsSupportedReturnsTrueWhenSupportedBySecondFactory(): void
    {
        $transport = $this->createMock(Transport::class);

        $this->innerFactory1->expects(self::once())
            ->method('isSupported')
            ->with($transport)
            ->willReturn(false);

        $this->innerFactory2->expects(self::once())
            ->method('isSupported')
            ->with($transport)
            ->willReturn(true);

        self::assertTrue($this->factory->isSupported($transport));
    }

    public function testIsSupportedReturnsFalseWhenNoFactorySupportsTransport(): void
    {
        $transport = $this->createMock(Transport::class);

        $this->innerFactory1->expects(self::once())
            ->method('isSupported')
            ->with($transport)
            ->willReturn(false);

        $this->innerFactory2->expects(self::once())
            ->method('isSupported')
            ->with($transport)
            ->willReturn(false);

        self::assertFalse($this->factory->isSupported($transport));
    }
}
