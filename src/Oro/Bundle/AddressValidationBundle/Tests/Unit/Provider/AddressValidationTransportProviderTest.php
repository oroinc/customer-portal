<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Tests\Unit\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\AddressValidationBundle\DependencyInjection\Configuration;
use Oro\Bundle\AddressValidationBundle\Provider\AddressValidationTransportProvider;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AddressValidationTransportProviderTest extends TestCase
{
    private ConfigManager&MockObject $configManager;
    private AddressValidationTransportProvider $provider;
    private ObjectRepository&MockObject $channelRepository;

    #[\Override]
    protected function setUp(): void
    {
        $doctrine = $this->createMock(ManagerRegistry::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->provider = new AddressValidationTransportProvider($doctrine, $this->configManager);

        $this->channelRepository = $this->createMock(ObjectRepository::class);
        $doctrine->expects(self::any())
            ->method('getRepository')
            ->with(Channel::class)
            ->willReturn($this->channelRepository);
    }

    public function testGetAddressValidationTransportReturnsNullWhenNoChannelIdConfigured(): void
    {
        $this->configManager->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKeyByName(Configuration::ADDRESS_VALIDATION_SERVICE), false, false, null)
            ->willReturn(null);

        $transport = $this->provider->getAddressValidationTransport();

        self::assertNull($transport);
    }

    public function testGetAddressValidationTransportReturnsNullWhenChannelNotFound(): void
    {
        $channelId = 42;

        $this->configManager->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKeyByName(Configuration::ADDRESS_VALIDATION_SERVICE), false, false, null)
            ->willReturn($channelId);

        $this->channelRepository->expects(self::once())
            ->method('find')
            ->with($channelId)
            ->willReturn(null);

        $transport = $this->provider->getAddressValidationTransport();

        self::assertNull($transport);
    }

    public function testGetAddressValidationTransportReturnsNullWhenChannelHasNoTransport(): void
    {
        $channelId = 42;
        $channel = new Channel();

        $this->configManager->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKeyByName(Configuration::ADDRESS_VALIDATION_SERVICE), false, false, null)
            ->willReturn($channelId);

        $this->channelRepository->expects(self::once())
            ->method('find')
            ->with($channelId)
            ->willReturn($channel);

        $transport = $this->provider->getAddressValidationTransport();

        self::assertNull($transport);
    }

    public function testGetAddressValidationTransportReturnsTransport(): void
    {
        $channelId = 42;
        $transport = $this->createMock(Transport::class);
        $channel = (new Channel())
            ->setTransport($transport);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKeyByName(Configuration::ADDRESS_VALIDATION_SERVICE), false, false, null)
            ->willReturn($channelId);

        $this->channelRepository->expects(self::once())
            ->method('find')
            ->with($channelId)
            ->willReturn($channel);

        $result = $this->provider->getAddressValidationTransport();

        self::assertSame($transport, $result);
    }
}
