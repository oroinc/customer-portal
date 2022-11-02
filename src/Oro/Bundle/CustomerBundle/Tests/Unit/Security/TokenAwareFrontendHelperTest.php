<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\CustomerBundle\Security\TokenAwareFrontendHelper;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TokenAwareFrontendHelperTest extends \PHPUnit\Framework\TestCase
{
    private const BACKEND_PREFIX = '/admin';

    /** @var \PHPUnit\Framework\MockObject\MockObject|ApplicationState */
    private $applicationState;

    protected function setUp(): void
    {
        $this->applicationState = $this->createMock(ApplicationState::class);
        $this->applicationState->expects(self::any())
            ->method('isInstalled')
            ->willReturn(true);
    }

    private function getRequestStack(Request $currentRequest = null): RequestStack
    {
        $requestStack = new RequestStack();
        if (null !== $currentRequest) {
            $requestStack->push($currentRequest);
        }

        return $requestStack;
    }

    private function getTokenStorage(TokenInterface $currentToken = null): TokenStorageInterface
    {
        $tokenStorage = new TokenStorage();
        if (null !== $currentToken) {
            $tokenStorage->setToken($currentToken);
        }

        return $tokenStorage;
    }

    public function testIsFrontendRequestWithNotAuthenticatedToken()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())
            ->method('isAuthenticated')
            ->willReturn(false);
        $token->expects(self::never())
            ->method('getUser');

        $helper = new TokenAwareFrontendHelper(
            self::BACKEND_PREFIX,
            $this->getRequestStack(),
            $this->applicationState,
            $this->getTokenStorage($token)
        );
        $this->assertFalse($helper->isFrontendRequest());
    }

    public function testIsFrontendRequestWithFrontendToken()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())
            ->method('isAuthenticated')
            ->willReturn(true);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn($this->createMock(CustomerUser::class));

        $helper = new TokenAwareFrontendHelper(
            self::BACKEND_PREFIX,
            $this->getRequestStack(),
            $this->applicationState,
            $this->getTokenStorage($token)
        );
        $this->assertTrue($helper->isFrontendRequest());
    }

    public function testIsFrontendRequestWithAnonymousCustomerUserToken()
    {
        $token = $this->createMock(AnonymousCustomerUserToken::class);
        $token->expects(self::once())
            ->method('isAuthenticated')
            ->willReturn(true);
        $token->expects(self::never())
            ->method('getUser');

        $helper = new TokenAwareFrontendHelper(
            self::BACKEND_PREFIX,
            $this->getRequestStack(),
            $this->applicationState,
            $this->getTokenStorage($token)
        );
        $this->assertTrue($helper->isFrontendRequest());
    }

    public function testIsFrontendRequestWithBackendToken()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())
            ->method('isAuthenticated')
            ->willReturn(true);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn($this->createMock(User::class));

        $helper = new TokenAwareFrontendHelper(
            self::BACKEND_PREFIX,
            $this->getRequestStack(),
            $this->applicationState,
            $this->getTokenStorage($token)
        );
        $this->assertFalse($helper->isFrontendRequest());
    }

    public function testIsFrontendRequestWithFrontendUrl()
    {
        $request = Request::create('/frontend');

        $helper = new TokenAwareFrontendHelper(
            self::BACKEND_PREFIX,
            $this->getRequestStack($request),
            $this->applicationState,
            $this->getTokenStorage()
        );
        $this->assertTrue($helper->isFrontendRequest());
    }

    public function testIsFrontendRequestWithBackendUrl()
    {
        $request = Request::create(self::BACKEND_PREFIX . '/backend');

        $helper = new TokenAwareFrontendHelper(
            self::BACKEND_PREFIX,
            $this->getRequestStack($request),
            $this->applicationState,
            $this->getTokenStorage()
        );
        $this->assertFalse($helper->isFrontendRequest());
    }

    public function testIsFrontendRequestWithoutPath()
    {
        $helper = new TokenAwareFrontendHelper(
            self::BACKEND_PREFIX,
            $this->getRequestStack(),
            $this->applicationState,
            $this->getTokenStorage()
        );
        $this->assertFalse($helper->isFrontendRequest());
    }

    public function testIsFrontendUrlForBackendUrl()
    {
        $helper = new TokenAwareFrontendHelper(
            self::BACKEND_PREFIX,
            $this->getRequestStack(),
            $this->applicationState,
            $this->getTokenStorage()
        );
        $this->assertFalse($helper->isFrontendUrl(self::BACKEND_PREFIX . '/test'));
    }

    public function testIsFrontendUrl()
    {
        $helper = new TokenAwareFrontendHelper(
            self::BACKEND_PREFIX,
            $this->getRequestStack(),
            $this->applicationState,
            $this->getTokenStorage()
        );
        $this->assertTrue($helper->isFrontendUrl('/test'));
    }
}
