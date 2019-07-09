<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Acl\AccessRule;

use Oro\Bundle\CustomerBundle\Acl\AccessRule\FrontendAccessRuleOptionMatcher;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\SecurityBundle\AccessRule\AccessRuleOptionMatcherInterface;
use Oro\Bundle\SecurityBundle\AccessRule\Criteria;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class FrontendAccessRuleOptionMatcherTest extends \PHPUnit\Framework\TestCase
{
    /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenStorage;

    /** @var AccessRuleOptionMatcherInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $innerMatcher;

    /** @var FrontendAccessRuleOptionMatcher */
    private $frontendMatcher;

    protected function setUp()
    {
        $this->innerMatcher = $this->createMock(AccessRuleOptionMatcherInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->frontendMatcher = new FrontendAccessRuleOptionMatcher(
            $this->innerMatcher,
            $this->tokenStorage
        );
    }

    public function testNotFrontendOption()
    {
        $criteria = $this->createMock(Criteria::class);
        $optionName = 'test_name';
        $optionValue = 'test_value';
        $result = true;

        $this->innerMatcher->expects(self::once())
            ->method('matches')
            ->with(self::identicalTo($criteria), $optionName, $optionValue)
            ->willReturn($result);

        self::assertSame(
            $result,
            $this->frontendMatcher->matches($criteria, $optionName, $optionValue)
        );
    }

    public function testFrontendOptionEqualsToTrueAndFrontendContext()
    {
        $criteria = $this->createMock(Criteria::class);
        $token = $this->createMock(TokenInterface::class);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn($this->createMock(CustomerUser::class));
        $this->innerMatcher->expects(self::never())
            ->method('matches');

        self::assertTrue(
            $this->frontendMatcher->matches($criteria, 'frontend', true)
        );
    }

    public function testFrontendOptionEqualsToTrueAndNotFrontendContext()
    {
        $criteria = $this->createMock(Criteria::class);
        $token = $this->createMock(TokenInterface::class);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn($this->createMock(User::class));
        $this->innerMatcher->expects(self::never())
            ->method('matches');

        self::assertFalse(
            $this->frontendMatcher->matches($criteria, 'frontend', true)
        );
    }

    public function testFrontendOptionEqualsToFalseAndFrontendContext()
    {
        $criteria = $this->createMock(Criteria::class);
        $token = $this->createMock(TokenInterface::class);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn($this->createMock(CustomerUser::class));
        $this->innerMatcher->expects(self::never())
            ->method('matches');

        self::assertFalse(
            $this->frontendMatcher->matches($criteria, 'frontend', false)
        );
    }

    public function testFrontendOptionEqualsToFalseAndNotFrontendContext()
    {
        $criteria = $this->createMock(Criteria::class);
        $token = $this->createMock(TokenInterface::class);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn($this->createMock(User::class));
        $this->innerMatcher->expects(self::never())
            ->method('matches');

        self::assertTrue(
            $this->frontendMatcher->matches($criteria, 'frontend', false)
        );
    }

    public function testFrontendOptionForVisitor()
    {
        $criteria = $this->createMock(Criteria::class);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($this->createMock(AnonymousCustomerUserToken::class));
        $this->innerMatcher->expects(self::never())
            ->method('matches');

        self::assertTrue(
            $this->frontendMatcher->matches($criteria, 'frontend', true)
        );
    }

    public function testFrontendOptionWhenNoToken()
    {
        $criteria = $this->createMock(Criteria::class);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);
        $this->innerMatcher->expects(self::never())
            ->method('matches');

        self::assertFalse(
            $this->frontendMatcher->matches($criteria, 'frontend', true)
        );
    }
}
