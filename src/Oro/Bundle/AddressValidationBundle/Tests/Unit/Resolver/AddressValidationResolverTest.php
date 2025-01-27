<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Tests\Unit\Resolver;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressValidationBundle\Model\ResolvedAddress;
use Oro\Bundle\AddressValidationBundle\Provider\AddressValidationTransportProvider;
use Oro\Bundle\AddressValidationBundle\Resolver\AddressValidationResolver;
use Oro\Bundle\AddressValidationBundle\Resolver\AddressValidationResolverInterface;
use Oro\Bundle\AddressValidationBundle\Resolver\Factory\AddressValidationResolverFactoryInterface;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AddressValidationResolverTest extends TestCase
{
    private AddressValidationTransportProvider&MockObject $addressValidationTransportProvider;

    private AddressValidationResolverFactoryInterface&MockObject $addressValidationResolverFactory;

    private FeatureChecker&MockObject $featureChecker;

    private AddressValidationResolver $resolver;

    protected function setUp(): void
    {
        $this->addressValidationTransportProvider = $this->createMock(AddressValidationTransportProvider::class);
        $this->addressValidationResolverFactory = $this->createMock(AddressValidationResolverFactoryInterface::class);
        $this->featureChecker = $this->createMock(FeatureChecker::class);

        $this->resolver = new AddressValidationResolver(
            $this->addressValidationTransportProvider,
            $this->addressValidationResolverFactory
        );

        $this->resolver->setFeatureChecker($this->featureChecker);
        $this->resolver->addFeature('oro_address_validation');
    }

    public function testResolveWhenFeatureIsDisabled(): void
    {
        $this->featureChecker
            ->expects(self::once())
            ->method('isFeatureEnabled')
            ->with('oro_address_validation')
            ->willReturn(false);

        $address = $this->createMock(AbstractAddress::class);

        $this->addressValidationTransportProvider
            ->expects(self::never())
            ->method('getAddressValidationTransport');
        $this->addressValidationResolverFactory
            ->expects(self::never())
            ->method('isSupported');
        $this->addressValidationResolverFactory
            ->expects(self::never())
            ->method('createForTransport');

        $result = $this->resolver->resolve($address);

        self::assertSame([], $result);
    }

    public function testResolveWhenTransportIsNull(): void
    {
        $this->featureChecker
            ->expects(self::once())
            ->method('isFeatureEnabled')
            ->with('oro_address_validation')
            ->willReturn(true);

        $this->addressValidationTransportProvider
            ->expects(self::once())
            ->method('getAddressValidationTransport')
            ->willReturn(null);

        $address = $this->createMock(AbstractAddress::class);

        $this->addressValidationResolverFactory
            ->expects(self::never())
            ->method('isSupported');
        $this->addressValidationResolverFactory
            ->expects(self::never())
            ->method('createForTransport');

        $result = $this->resolver->resolve($address);

        self::assertSame([], $result);
    }

    public function testResolveWhenTransportIsNotSupported(): void
    {
        $this->featureChecker
            ->expects(self::once())
            ->method('isFeatureEnabled')
            ->with('oro_address_validation')
            ->willReturn(true);

        $transport = $this->createMock(Transport::class);

        $this->addressValidationTransportProvider
            ->expects(self::once())
            ->method('getAddressValidationTransport')
            ->willReturn($transport);

        $this->addressValidationResolverFactory
            ->expects(self::once())
            ->method('isSupported')
            ->with($transport)
            ->willReturn(false);

        $address = $this->createMock(AbstractAddress::class);

        $this->addressValidationResolverFactory
            ->expects(self::never())
            ->method('createForTransport');

        $result = $this->resolver->resolve($address);

        self::assertSame([], $result);
    }

    public function testResolveWhenTransportIsSupported(): void
    {
        $this->featureChecker
            ->expects(self::once())
            ->method('isFeatureEnabled')
            ->with('oro_address_validation')
            ->willReturn(true);

        $transport = $this->createMock(Transport::class);

        $this->addressValidationTransportProvider
            ->expects(self::once())
            ->method('getAddressValidationTransport')
            ->willReturn($transport);

        $this->addressValidationResolverFactory
            ->expects(self::once())
            ->method('isSupported')
            ->with($transport)
            ->willReturn(true);

        $innerResolver = $this->createMock(AddressValidationResolverInterface::class);

        $this->addressValidationResolverFactory
            ->expects(self::once())
            ->method('createForTransport')
            ->with($transport)
            ->willReturn($innerResolver);

        $address = $this->createMock(AbstractAddress::class);

        $resolvedAddress1 = new ResolvedAddress($address);
        $resolvedAddress2 = new ResolvedAddress($address);
        $innerResolver
            ->expects(self::once())
            ->method('resolve')
            ->with($address)
            ->willReturn([$resolvedAddress1, $resolvedAddress2]);

        $result = $this->resolver->resolve($address);

        self::assertSame([$resolvedAddress1, $resolvedAddress2], $result);
    }
}
