<?php

declare(strict_types=1);

namespace Oro\Bundle\AddressValidationBundle\Tests\Unit\FeatureToggle;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\AddressValidationBundle\FeatureToggle\ConfigVoter;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FeatureToggleBundle\Checker\Voter\VoterInterface;
use Oro\Bundle\FeatureToggleBundle\Configuration\ConfigurationManager;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ConfigVoterTest extends TestCase
{
    private ConfigManager&MockObject $configManager;
    private ConfigurationManager&MockObject $featureConfigManager;
    private ConfigVoter $voter;
    private ObjectRepository&MockObject $channelRepository;

    #[\Override]
    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->featureConfigManager = $this->createMock(ConfigurationManager::class);
        $doctrine = $this->createMock(ManagerRegistry::class);

        $this->voter = new ConfigVoter(
            $this->configManager,
            $this->featureConfigManager,
            $doctrine
        );

        $this->channelRepository = $this->createMock(ObjectRepository::class);
        $doctrine->expects(self::any())
            ->method('getRepository')
            ->with(Channel::class)
            ->willReturn($this->channelRepository);
    }

    public function testVoteAbstainForNonAddressValidationFeature(): void
    {
        $feature = 'some_other_feature';
        $result = $this->voter->vote($feature);

        self::assertSame(VoterInterface::FEATURE_ABSTAIN, $result);
    }

    public function testVoteAbstainWhenToggleIsMissing(): void
    {
        $this->featureConfigManager->expects(self::once())
            ->method('get')
            ->with('oro_address_validation', 'toggle')
            ->willReturn(null);

        $result = $this->voter->vote('oro_address_validation');

        self::assertSame(VoterInterface::FEATURE_ABSTAIN, $result);
    }

    public function testVoteDisabledWhenChannelIdIsMissing(): void
    {
        $this->featureConfigManager->expects(self::once())
            ->method('get')
            ->with('oro_address_validation', 'toggle')
            ->willReturn('oro_address_validation.address_validation_service');

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_address_validation.address_validation_service', false, false, null)
            ->willReturn(null);

        $result = $this->voter->vote('oro_address_validation');

        self::assertSame(VoterInterface::FEATURE_DISABLED, $result);
    }

    public function testVoteDisabledWhenChannelNotFound(): void
    {
        $this->featureConfigManager->expects(self::once())
            ->method('get')
            ->with('oro_address_validation', 'toggle')
            ->willReturn('oro_address_validation.address_validation_service');

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_address_validation.address_validation_service', false, false, null)
            ->willReturn(123);

        $this->channelRepository->expects(self::once())
            ->method('find')
            ->with(123)
            ->willReturn(null);

        $result = $this->voter->vote('oro_address_validation');

        self::assertSame(VoterInterface::FEATURE_DISABLED, $result);
    }

    public function testVoteEnabledWhenChannelExists(): void
    {
        $this->featureConfigManager->expects(self::once())
            ->method('get')
            ->with('oro_address_validation', 'toggle')
            ->willReturn('oro_address_validation.address_validation_service');

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_address_validation.address_validation_service', false, false, null)
            ->willReturn(123);

        $channel = $this->createMock(Channel::class);
        $this->channelRepository->expects(self::once())
            ->method('find')
            ->with(123)
            ->willReturn($channel);

        $result = $this->voter->vote('oro_address_validation');

        self::assertSame(VoterInterface::FEATURE_ENABLED, $result);
    }
}
