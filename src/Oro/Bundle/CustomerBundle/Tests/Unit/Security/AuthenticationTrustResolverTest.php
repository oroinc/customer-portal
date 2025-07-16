<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security;

use Oro\Bundle\CustomerBundle\Security\AuthenticationTrustResolver;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\UserBundle\Entity\AbstractUser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver as BaseAuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthenticationTrustResolverTest extends TestCase
{
    private BaseAuthenticationTrustResolver&MockObject $baseTrustResolver;
    private AuthenticationTrustResolver $resolver;

    #[\Override]
    protected function setUp(): void
    {
        $this->baseTrustResolver = $this->createMock(BaseAuthenticationTrustResolver::class);
        $this->resolver = new AuthenticationTrustResolver($this->baseTrustResolver);
    }

    public function testIsAnonymous(): void
    {
        $token = $this->getToken();
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($this->createMock(AbstractUser::class));
        $rememberMeToken = $this->getRememberMeToken();
        $rememberMeToken->expects($this->once())
            ->method('getUser')
            ->willReturn($this->createMock(AbstractUser::class));
        $this->assertFalse($this->resolver->isAnonymous(null));
        $this->assertFalse($this->resolver->isAnonymous($token));
        $this->assertFalse($this->resolver->isAnonymous($rememberMeToken));
        $this->assertTrue($this->resolver->isAnonymous($this->getAnonymousCustomerUserToken()));
    }

    public function testIsRememberMe(): void
    {
        $this->baseTrustResolver->expects($this->once())
            ->method('isRememberMe')
            ->willReturn(true);

        $this->assertTrue($this->resolver->isRememberMe($this->getToken()));
    }

    public function testisFullFledged(): void
    {
        $this->baseTrustResolver->expects($this->any())
            ->method('isFullFledged')
            ->willReturn(false);

        $this->assertFalse($this->resolver->isFullFledged(null));
        $this->assertFalse($this->resolver->isFullFledged($this->getAnonymousCustomerUserToken()));
        $this->assertFalse($this->resolver->isFullFledged($this->getRememberMeToken()));
        $this->assertFalse($this->resolver->isFullFledged($this->getToken()));
    }

    protected function getToken()
    {
        return $this->createMock(TokenInterface::class);
    }

    protected function getAnonymousCustomerUserToken()
    {
        return $this->createMock(AnonymousCustomerUserToken::class);
    }

    protected function getRememberMeToken()
    {
        return $this->createMock(RememberMeToken::class);
    }
}
