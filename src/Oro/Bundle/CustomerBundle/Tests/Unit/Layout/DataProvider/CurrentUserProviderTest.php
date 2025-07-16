<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Layout\DataProvider;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Entity\CustomerVisitor;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\CurrentUserProvider;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\UserBundle\Entity\AbstractUser;
use Oro\Bundle\UserBundle\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CurrentUserProviderTest extends TestCase
{
    private TokenStorageInterface&MockObject $tokenStorage;
    private CurrentUserProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->provider = new CurrentUserProvider($this->tokenStorage);
    }

    public function testGetCurrentUserWhenTokenContainsSupportedUser(): void
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

    public function testGetCurrentUserWhenTokenContainsNotSupportedUser(): void
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

    public function testGetCurrentUserWhenTokenDoesNotContainUser(): void
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

    public function testGetCurrentUserWhenTokenDoesNotExist(): void
    {
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        self::assertNull($this->provider->getCurrentUser());
    }

    public function testIsFrontendRequestForNotAuthenticatedToken(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())
            ->method('getUser');

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertFalse($this->provider->isFrontendRequest());
    }

    public function testIsFrontendRequestForFrontendToken(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::exactly(2))
            ->method('getUser')
            ->willReturn($this->createMock(CustomerUser::class));

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertTrue($this->provider->isFrontendRequest());
    }

    public function testIsFrontendRequestFoeAnonymousCustomerUserToken(): void
    {
        $token = $this->createMock(AnonymousCustomerUserToken::class);
        // anonymous customer visitor
        $token->expects(self::once())
            ->method('getUser')
        ->willReturn(new CustomerVisitor());

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertTrue($this->provider->isFrontendRequest());
    }

    public function testIsFrontendRequestForBackendToken(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::exactly(2))
            ->method('getUser')
            ->willReturn($this->createMock(User::class));

        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($token);

        $this->assertFalse($this->provider->isFrontendRequest());
    }

    public function testIsFrontendRequestWhenTokenDoesNotExist(): void
    {
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn(null);

        $this->assertFalse($this->provider->isFrontendRequest());
    }
}
