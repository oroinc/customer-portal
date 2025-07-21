<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Voter;

use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\CustomerBundle\Voter\AnonymousCustomerUserVoter;
use Oro\Bundle\FeatureToggleBundle\Checker\Voter\VoterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AnonymousCustomerUserVoterTest extends TestCase
{
    private VoterInterface&MockObject $configVoter;
    private TokenStorageInterface&MockObject $tokenStorage;
    private AnonymousCustomerUserVoter $voter;

    #[\Override]
    protected function setUp(): void
    {
        $this->configVoter = $this->createMock(VoterInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->voter = new AnonymousCustomerUserVoter($this->configVoter, $this->tokenStorage);
    }

    public function testVoteAbstainForAnotherFeature(): void
    {
        $vote = $this->voter->vote('some_feature');
        $this->assertEquals(VoterInterface::FEATURE_ABSTAIN, $vote);
    }

    public function testVoteAbstainForNotAnonymousUser(): void
    {
        $featureName = 'feature_name';

        $token = $this->createMock(TokenInterface::class);
        $scopeIdentifier = 1;
        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);
        $this->configVoter->expects($this->never())
            ->method('vote')
            ->with($featureName, $scopeIdentifier)
            ->willReturn(VoterInterface::FEATURE_ENABLED);

        $this->voter->setFeatureName($featureName);

        $vote = $this->voter->vote($featureName, $scopeIdentifier);
        $this->assertEquals(VoterInterface::FEATURE_ABSTAIN, $vote);
    }

    public function testVoteEnabled(): void
    {
        $featureName = 'feature_name';

        $token = $this->createMock(AnonymousCustomerUserToken::class);
        $scopeIdentifier = 1;
        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);
        $this->configVoter->expects($this->once())
            ->method('vote')
            ->with($featureName, $scopeIdentifier)
            ->willReturn(VoterInterface::FEATURE_ENABLED);

        $this->voter->setFeatureName($featureName);

        $vote = $this->voter->vote($featureName, $scopeIdentifier);
        $this->assertEquals(VoterInterface::FEATURE_ENABLED, $vote);
    }

    public function testVoteDisabled(): void
    {
        $featureName = 'feature_name';

        $token = $this->createMock(AnonymousCustomerUserToken::class);
        $scopeIdentifier = 1;
        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);
        $this->configVoter->expects($this->once())
            ->method('vote')
            ->with($featureName, $scopeIdentifier)
            ->willReturn(VoterInterface::FEATURE_DISABLED);

        $this->voter->setFeatureName($featureName);

        $vote = $this->voter->vote($featureName, $scopeIdentifier);
        $this->assertEquals(VoterInterface::FEATURE_DISABLED, $vote);
    }
}
