<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Voter;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Voter\CustomerUserVoter;
use Oro\Bundle\FeatureToggleBundle\Checker\Voter\VoterInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerUserVoterTest extends TestCase
{
    private TokenAccessorInterface&MockObject $tokenAccessor;
    private CustomerUserVoter $voter;

    #[\Override]
    protected function setUp(): void
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $this->voter = new CustomerUserVoter($this->tokenAccessor);
    }

    public function testVoteAbstainForAnotherFeature(): void
    {
        $vote = $this->voter->vote('some_feature');
        $this->assertEquals(VoterInterface::FEATURE_ABSTAIN, $vote);
    }

    /**
     * @dataProvider getUser
     */
    public function testVote(?object $user, int $expectedResult): void
    {
        $featureName = 'feature_name';

        $scopeIdentifier = 1;
        $this->tokenAccessor->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->voter->setFeatureName($featureName);

        $vote = $this->voter->vote($featureName, $scopeIdentifier);
        $this->assertEquals($expectedResult, $vote);
    }

    public function getUser(): array
    {
        return [
            'incorrect user object' => [
                'user' => new \stdClass(),
                'expectedResult' => VoterInterface::FEATURE_ABSTAIN
            ],
            'customer user' => [
                'user' => new CustomerUser(),
                'expectedResult' => VoterInterface::FEATURE_ENABLED
            ],
            'user as null' => [
                'user' => null,
                'expectedResult' => VoterInterface::FEATURE_ABSTAIN
            ],
        ];
    }
}
