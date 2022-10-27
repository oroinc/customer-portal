<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Layout\DataProvider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\CurrentUserProvider;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\UserBundle\Entity\AbstractUser;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CurrentUserProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenStorage;

    /** @var CurrentUserProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->provider = new CurrentUserProvider($this->tokenStorage);
    }

    public function testGetCurrentUserWhenTokenContainsSupportedUser()
    {
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(AbstractUser::class);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $this->assertSame($user, $this->provider->getCurrentUser());
    }

    public function testGetCurrentUserWhenTokenContainsNotSupportedUser()
    {
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $this->assertNull($this->provider->getCurrentUser());
    }

    public function testGetCurrentUserWhenTokenDoesNotContainUser()
    {
        $token = $this->createMock(TokenInterface::class);

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn(null);

        self::assertNull($this->provider->getCurrentUser());
    }

    public function testGetCurrentUserWhenTokenDoesNotExist()
    {
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        self::assertNull($this->provider->getCurrentUser());
    }

    public function testIsFrontendRequestForNotAuthenticatedToken()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())
            ->method('isAuthenticated')
            ->willReturn(false);
        $token->expects(self::never())
            ->method('getUser');

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertFalse($this->provider->isFrontendRequest());
    }

    public function testIsFrontendRequestForFrontendToken()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())
            ->method('isAuthenticated')
            ->willReturn(true);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn($this->createMock(CustomerUser::class));

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertTrue($this->provider->isFrontendRequest());
    }

    public function testIsFrontendRequestFoeAnonymousCustomerUserToken()
    {
        $token = $this->createMock(AnonymousCustomerUserToken::class);
        $token->expects(self::once())
            ->method('isAuthenticated')
            ->willReturn(true);
        $token->expects(self::never())
            ->method('getUser');

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertTrue($this->provider->isFrontendRequest());
    }

    public function testIsFrontendRequestForBackendToken()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())
            ->method('isAuthenticated')
            ->willReturn(true);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn($this->createMock(User::class));

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertFalse($this->provider->isFrontendRequest());
    }

    public function testIsFrontendRequestWhenTokenDoesNotExist()
    {
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->assertFalse($this->provider->isFrontendRequest());
    }
}
