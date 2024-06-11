<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\CustomerBundle\Security\TokenAwareFrontendHelper;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\UserBundle\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TokenAwareFrontendHelperTest extends TestCase
{
    private const BACKEND_PREFIX = '/admin';

    private MockObject|ApplicationState $applicationState;

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

    public function testIsFrontendRequestWithFrontendToken(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn($this->createMock(CustomerUser::class));

        $helper = new TokenAwareFrontendHelper(
            self::BACKEND_PREFIX,
            $this->getRequestStack(),
            $this->applicationState,
            $this->getTokenStorage($token)
        );
        self::assertTrue($helper->isFrontendRequest());
    }

    public function testIsFrontendRequestWithAnonymousCustomerUserToken(): void
    {
        $token = $this->createMock(AnonymousCustomerUserToken::class);
        $token->expects(self::never())
            ->method('getUser');

        $helper = new TokenAwareFrontendHelper(
            self::BACKEND_PREFIX,
            $this->getRequestStack(),
            $this->applicationState,
            $this->getTokenStorage($token)
        );
        self::assertTrue($helper->isFrontendRequest());
    }

    public function testIsFrontendRequestWithBackendToken(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())
            ->method('getUser')
            ->willReturn($this->createMock(User::class));

        $helper = new TokenAwareFrontendHelper(
            self::BACKEND_PREFIX,
            $this->getRequestStack(),
            $this->applicationState,
            $this->getTokenStorage($token)
        );
        self::assertFalse($helper->isFrontendRequest());
    }

    public function testIsFrontendRequestWithFrontendUrl(): void
    {
        $request = Request::create('/frontend');

        $helper = new TokenAwareFrontendHelper(
            self::BACKEND_PREFIX,
            $this->getRequestStack($request),
            $this->applicationState,
            $this->getTokenStorage()
        );
        self::assertTrue($helper->isFrontendRequest());
    }

    public function testIsFrontendRequestWithBackendUrl(): void
    {
        $request = Request::create(self::BACKEND_PREFIX . '/backend');

        $helper = new TokenAwareFrontendHelper(
            self::BACKEND_PREFIX,
            $this->getRequestStack($request),
            $this->applicationState,
            $this->getTokenStorage()
        );
        self::assertFalse($helper->isFrontendRequest());
    }

    public function testIsFrontendRequestWithoutPath(): void
    {
        $helper = new TokenAwareFrontendHelper(
            self::BACKEND_PREFIX,
            $this->getRequestStack(),
            $this->applicationState,
            $this->getTokenStorage()
        );
        self::assertFalse($helper->isFrontendRequest());
    }

    public function testIsFrontendRequestWhenEmulated(): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage
            ->expects(self::never())
            ->method(self::anything());

        $helper = new TokenAwareFrontendHelper(
            self::BACKEND_PREFIX,
            $this->getRequestStack(),
            $this->applicationState,
            $tokenStorage
        );

        $helper->emulateFrontendRequest();
        self::assertTrue($helper->isFrontendRequest());
    }

    public function testIsFrontendUrlForBackendUrl(): void
    {
        $helper = new TokenAwareFrontendHelper(
            self::BACKEND_PREFIX,
            $this->getRequestStack(),
            $this->applicationState,
            $this->getTokenStorage()
        );
        self::assertFalse($helper->isFrontendUrl(self::BACKEND_PREFIX . '/test'));
    }

    public function testIsFrontendUrl(): void
    {
        $helper = new TokenAwareFrontendHelper(
            self::BACKEND_PREFIX,
            $this->getRequestStack(),
            $this->applicationState,
            $this->getTokenStorage()
        );
        self::assertTrue($helper->isFrontendUrl('/test'));
    }
}
