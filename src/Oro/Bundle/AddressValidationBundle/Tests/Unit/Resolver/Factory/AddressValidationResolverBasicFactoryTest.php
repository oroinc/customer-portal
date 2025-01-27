<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Tests\Unit\Resolver\Factory;

use Oro\Bundle\AddressValidationBundle\Client\AddressValidationClientInterface;
use Oro\Bundle\AddressValidationBundle\Client\Request\Factory\AddressValidationRequestFactoryInterface;
use Oro\Bundle\AddressValidationBundle\ResolvedAddress\Factory\ResolvedAddressFactoryInterface;
use Oro\Bundle\AddressValidationBundle\Resolver\AddressValidationBasicResolver;
use Oro\Bundle\AddressValidationBundle\Resolver\Factory\AddressValidationResolverBasicFactory;
use Oro\Bundle\IntegrationBundle\Entity\Stub\TestTransport1Settings;
use Oro\Bundle\IntegrationBundle\Entity\Stub\TestTransport2Settings;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AddressValidationResolverBasicFactoryTest extends TestCase
{
    private AddressValidationRequestFactoryInterface&MockObject $addressValidationRequestFactory;

    private AddressValidationClientInterface&MockObject $addressValidationClient;

    private ResolvedAddressFactoryInterface&MockObject $resolvedAddressFactory;

    private AddressValidationResolverBasicFactory $factory;

    protected function setUp(): void
    {
        $this->addressValidationRequestFactory = $this->createMock(AddressValidationRequestFactoryInterface::class);
        $this->addressValidationClient = $this->createMock(AddressValidationClientInterface::class);
        $this->resolvedAddressFactory = $this->createMock(ResolvedAddressFactoryInterface::class);
        $supportedTransport = TestTransport1Settings::class;

        $this->factory = new AddressValidationResolverBasicFactory(
            $this->addressValidationRequestFactory,
            $this->addressValidationClient,
            $this->resolvedAddressFactory,
            $supportedTransport
        );
    }

    public function testCreateForTransport(): void
    {
        $transport = $this->createMock(Transport::class);

        $resolver = $this->factory->createForTransport($transport);

        $expected = new AddressValidationBasicResolver(
            $this->addressValidationRequestFactory,
            $this->addressValidationClient,
            $this->resolvedAddressFactory,
            $transport
        );

        self::assertEquals($expected, $resolver);
    }

    public function testIsSupportedReturnsTrue(): void
    {
        self::assertTrue($this->factory->isSupported(new TestTransport1Settings()));
    }

    public function testIsSupportedReturnsFalse(): void
    {
        self::assertFalse($this->factory->isSupported(new TestTransport2Settings()));
    }
}
