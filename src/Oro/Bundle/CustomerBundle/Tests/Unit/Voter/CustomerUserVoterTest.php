<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Voter;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Voter\CustomerUserVoter;
use Oro\Bundle\FeatureToggleBundle\Checker\Voter\VoterInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

class CustomerUserVoterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $tokenAccessor;

    /**
     * @var CustomerUserVoter
     */
    private $voter;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->voter         = new CustomerUserVoter($this->tokenAccessor);
    }

    public function testVoteAbstainForAnotherFeature()
    {
        $vote = $this->voter->vote('some_feature');
        $this->assertEquals(VoterInterface::FEATURE_ABSTAIN, $vote);
    }

    /**
     * @param mixed  $user
     * @param string $expectedResult
     *
     * @dataProvider getUser
     */
    public function testVote($user, $expectedResult)
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

    /**
     * @return array
     */
    public function getUser()
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
