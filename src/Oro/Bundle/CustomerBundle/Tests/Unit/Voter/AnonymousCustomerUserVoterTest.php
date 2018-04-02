<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Voter;

use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\CustomerBundle\Voter\AnonymousCustomerUserVoter;
use Oro\Bundle\FeatureToggleBundle\Checker\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AnonymousCustomerUserVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VoterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configVoter;

    /**
     * @var TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tokenStorage;

    /**
     * @var AnonymousCustomerUserVoter
     */
    private $voter;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->configVoter = $this->createMock(VoterInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->voter = new AnonymousCustomerUserVoter($this->configVoter, $this->tokenStorage);
    }

    public function testVoteAbstainForAnotherFeature()
    {
        $vote = $this->voter->vote('some_feature');
        $this->assertEquals(VoterInterface::FEATURE_ABSTAIN, $vote);
    }

    public function testVoteAbstainForNotAnonymousUser()
    {
        $featureName = 'feature_name';

        $token = new \stdClass();
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

    public function testVoteEnabled()
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

    public function testVoteDisabled()
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
