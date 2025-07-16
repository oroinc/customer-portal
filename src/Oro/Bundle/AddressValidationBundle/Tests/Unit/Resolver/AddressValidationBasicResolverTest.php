<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Tests\Unit\Resolver;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressValidationBundle\Client\AddressValidationClientInterface;
use Oro\Bundle\AddressValidationBundle\Client\Request\AddressValidationRequestInterface;
use Oro\Bundle\AddressValidationBundle\Client\Request\Factory\AddressValidationRequestFactoryInterface;
use Oro\Bundle\AddressValidationBundle\Client\Response\AddressValidationResponseInterface;
use Oro\Bundle\AddressValidationBundle\Model\ResolvedAddress;
use Oro\Bundle\AddressValidationBundle\ResolvedAddress\Factory\ResolvedAddressFactoryInterface;
use Oro\Bundle\AddressValidationBundle\Resolver\AddressValidationBasicResolver;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AddressValidationBasicResolverTest extends TestCase
{
    private AddressValidationRequestFactoryInterface&MockObject $addressValidationRequestFactory;
    private AddressValidationClientInterface&MockObject $addressValidationClient;
    private ResolvedAddressFactoryInterface&MockObject $resolvedAddressFactory;
    private Transport&MockObject $transport;
    private AddressValidationBasicResolver $resolver;

    #[\Override]
    protected function setUp(): void
    {
        $this->addressValidationRequestFactory = $this->createMock(AddressValidationRequestFactoryInterface::class);
        $this->addressValidationClient = $this->createMock(AddressValidationClientInterface::class);
        $this->resolvedAddressFactory = $this->createMock(ResolvedAddressFactoryInterface::class);
        $this->transport = $this->createMock(Transport::class);

        $this->resolver = new AddressValidationBasicResolver(
            $this->addressValidationRequestFactory,
            $this->addressValidationClient,
            $this->resolvedAddressFactory,
            $this->transport
        );
    }

    public function testResolveWithValidAddresses(): void
    {
        $address = $this->createMock(AbstractAddress::class);
        $request = $this->createMock(AddressValidationRequestInterface::class);
        $response = $this->createMock(AddressValidationResponseInterface::class);

        $rawAddress1 = ['country' => 'US', 'city' => 'New York'];
        $rawAddress2 = ['country' => 'US', 'city' => 'San Francisco'];

        $resolvedAddress1 = (new ResolvedAddress($address))->setCity('New York');
        $resolvedAddress2 = (new ResolvedAddress($address))->setCity('Los Angeles');

        $this->addressValidationRequestFactory->expects(self::once())
            ->method('create')
            ->with($address)
            ->willReturn($request);

        $this->addressValidationClient->expects(self::once())
            ->method('send')
            ->with($request, $this->transport)
            ->willReturn($response);

        $response->expects(self::once())
            ->method('getResolvedAddresses')
            ->willReturn([$rawAddress1, $rawAddress2]);

        $this->resolvedAddressFactory->expects(self::exactly(2))
            ->method('createResolvedAddress')
            ->withConsecutive([$rawAddress1, $address], [$rawAddress2, $address])
            ->willReturnOnConsecutiveCalls($resolvedAddress1, $resolvedAddress2);

        $result = $this->resolver->resolve($address);

        self::assertSame([$resolvedAddress1, $resolvedAddress2], $result);
    }

    public function testResolveWithNullResolvedAddress(): void
    {
        $address = $this->createMock(AbstractAddress::class);
        $request = $this->createMock(AddressValidationRequestInterface::class);
        $response = $this->createMock(AddressValidationResponseInterface::class);

        $rawAddress1 = ['country' => 'US', 'city' => 'New York'];
        $rawAddress2 = ['country' => 'US', 'city' => 'San Francisco'];

        $this->addressValidationRequestFactory->expects(self::once())
            ->method('create')
            ->with($address)
            ->willReturn($request);

        $this->addressValidationClient->expects(self::once())
            ->method('send')
            ->with($request, $this->transport)
            ->willReturn($response);

        $response->expects(self::once())
            ->method('getResolvedAddresses')
            ->willReturn([$rawAddress1, $rawAddress2]);

        $this->resolvedAddressFactory->expects(self::exactly(2))
            ->method('createResolvedAddress')
            ->withConsecutive([$rawAddress1, $address], [$rawAddress2, $address])
            ->willReturnOnConsecutiveCalls(null, null);

        $result = $this->resolver->resolve($address);

        self::assertSame([], $result);
    }

    public function testResolveWithEmptyResolvedAddresses(): void
    {
        $address = $this->createMock(AbstractAddress::class);
        $request = $this->createMock(AddressValidationRequestInterface::class);
        $response = $this->createMock(AddressValidationResponseInterface::class);

        $this->addressValidationRequestFactory->expects(self::once())
            ->method('create')
            ->with($address)
            ->willReturn($request);

        $this->addressValidationClient->expects(self::once())
            ->method('send')
            ->with($request, $this->transport)
            ->willReturn($response);

        $response->expects(self::once())
            ->method('getResolvedAddresses')
            ->willReturn([]);

        $result = $this->resolver->resolve($address);

        self::assertSame([], $result);
    }
}
