<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\LoginManager;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationTokenFactoryInterface;
use Oro\Bundle\SecurityBundle\Model\Role;
use Oro\Bundle\UserBundle\Entity\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Http\RememberMe\RememberMeHandlerInterface;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;

class LoginManagerTest extends TestCase
{
    private TokenStorageInterface&MockObject $tokenStorage;
    private UserCheckerInterface&MockObject $userChecker;
    private SessionAuthenticationStrategyInterface&MockObject $sessionStrategy;
    private Request&MockObject $request;
    private RequestStack&MockObject $requestStack;
    private RememberMeHandlerInterface&MockObject $rememberMe;
    private UsernamePasswordOrganizationTokenFactoryInterface&MockObject $tokenFactory;
    private EventDispatcherInterface&MockObject $eventDispatcher;
    private LoginManager $loginManager;

    #[\Override]
    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->userChecker = $this->createMock(UserCheckerInterface::class);
        $this->request = $this->createMock(Request::class);
        $this->sessionStrategy = $this->createMock(SessionAuthenticationStrategyInterface::class);

        $this->requestStack = $this->createMock(RequestStack::class);
        $this->requestStack->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn($this->request);
        $this->rememberMe = $this->createMock(RememberMeHandlerInterface::class);

        $this->tokenFactory = $this->createMock(UsernamePasswordOrganizationTokenFactoryInterface::class);

        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->loginManager = new LoginManager(
            $this->tokenStorage,
            $this->userChecker,
            $this->sessionStrategy,
            $this->requestStack,
            $this->tokenFactory,
            $this->eventDispatcher,
            $this->rememberMe
        );
    }

    public function testLogInUserWithRequest(): void
    {
        $roles = [new Role('SAMPLE_ROLE_1')];

        $user = new CustomerUser();
        $user->setOrganization(new Organization());
        $user->setUserRoles($roles);

        $token = $this->createMock(UsernamePasswordToken::class);

        $this->tokenStorage->expects(self::once())
            ->method('setToken')
            ->with($this->isInstanceOf(TokenInterface::class));

        $this->userChecker->expects(self::once())
            ->method('checkPreAuth')
            ->with($this->isInstanceOf(UserInterface::class));

        $this->sessionStrategy->expects(self::once())
            ->method('onAuthentication')
            ->with($this->request, $this->isInstanceOf(TokenInterface::class));

        $this->tokenFactory->expects(self::once())
            ->method('create')
            ->with(
                $this->isInstanceOf(CustomerUser::class),
                'main',
                $this->isInstanceOf(Organization::class),
                $roles
            )
            ->willReturn($token);

        $this->loginManager->logInUser('main', $user);
    }

    public function testLogInUserWithRememberMeAndRequest(): void
    {
        $response = $this->createMock(Response::class);

        $roles = [new Role('SAMPLE_ROLE_1')];

        $user = new CustomerUser();
        $user->setOrganization(new Organization());
        $user->setUserRoles($roles);

        $token = $this->createMock(UsernamePasswordToken::class);

        $this->tokenStorage->expects(self::once())
            ->method('setToken')
            ->with($this->isInstanceOf(TokenInterface::class));

        $this->userChecker->expects(self::once())
            ->method('checkPreAuth')
            ->with($this->isInstanceOf(UserInterface::class));

        $this->sessionStrategy->expects(self::once())
            ->method('onAuthentication')
            ->with($this->request, $this->isInstanceOf(TokenInterface::class));

        $this->tokenFactory->expects(self::once())
            ->method('create')
            ->with(
                $this->isInstanceOf(CustomerUser::class),
                'main',
                $this->isInstanceOf(Organization::class),
                $roles
            )
            ->willReturn($token);

        $this->rememberMe->expects(self::once())
            ->method('createRememberMeCookie')
            ->with($user);

        $this->loginManager->logInUser('main', $user, $response);
    }
}
