<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\LoginManager;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationTokenFactoryInterface;
use Oro\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;

class LoginManagerTest extends \PHPUnit\Framework\TestCase
{
    /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenStorage;

    /** @var UserCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $userChecker;

    /** @var SessionAuthenticationStrategyInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $sessionStrategy;

    /** @var Request|\PHPUnit\Framework\MockObject\MockObject */
    private $request;

    /** @var RequestStack|\PHPUnit\Framework\MockObject\MockObject */
    private $requestStack;

    /** @var RememberMeServicesInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $rememberMe;

    /** @var UsernamePasswordOrganizationTokenFactoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenFactory;

    /** @var  EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $eventDispatcher;

    /** @var LoginManager */
    private $loginManager;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();

        $this->userChecker = $this->getMockBuilder(UserCheckerInterface::class)->getMock();

        $this->request = $this->getMockBuilder(Request::class)->getMock();

        $this->sessionStrategy = $this->getMockBuilder(SessionAuthenticationStrategyInterface::class)->getMock();

        $this->requestStack = $this->getMockBuilder(RequestStack::class)->getMock();
        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->will($this->returnValue($this->request));
        $this->rememberMe = $this->getMockBuilder(RememberMeServicesInterface::class)->getMock();

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

    public function testLogInUserWithRequest()
    {
        $roles = [$this->createMock(Role::class)];

        $user = new CustomerUser();
        $user->setOrganization(new Organization());
        $user->setRoles($roles);

        $token = $this->createMock(UsernamePasswordToken::class);

        $this->tokenStorage
            ->expects($this->once())
            ->method('setToken')
            ->with($this->isInstanceOf(TokenInterface::class));

        $this->userChecker
            ->expects($this->once())
            ->method('checkPreAuth')
            ->with($this->isInstanceOf(UserInterface::class));

        $this->sessionStrategy
            ->expects($this->once())
            ->method('onAuthentication')
            ->with($this->request, $this->isInstanceOf(TokenInterface::class));

        $this->tokenFactory->expects($this->once())
            ->method('create')
            ->with(
                $this->isInstanceOf(CustomerUser::class),
                null,
                'main',
                $this->isInstanceOf(Organization::class),
                $roles
            )
            ->willReturn($token);

        $this->loginManager->logInUser('main', $user);
    }

    public function testLogInUserWithRememberMeAndRequest()
    {
        $response = $this->getMockBuilder(Response::class)->getMock();

        $roles = [$this->createMock(Role::class)];

        $user = new CustomerUser();
        $user->setOrganization(new Organization());
        $user->setRoles($roles);

        $token = $this->createMock(UsernamePasswordToken::class);

        $this->tokenStorage
            ->expects($this->once())
            ->method('setToken')
            ->with($this->isInstanceOf(TokenInterface::class));

        $this->userChecker
            ->expects($this->once())
            ->method('checkPreAuth')
            ->with($this->isInstanceOf(UserInterface::class));

        $this->sessionStrategy
            ->expects($this->once())
            ->method('onAuthentication')
            ->with($this->request, $this->isInstanceOf(TokenInterface::class));

        $this->tokenFactory->expects($this->once())
            ->method('create')
            ->with(
                $this->isInstanceOf(CustomerUser::class),
                null,
                'main',
                $this->isInstanceOf(Organization::class),
                $roles
            )
            ->willReturn($token);

        $this->rememberMe->expects($this->once())
            ->method('loginSuccess')
            ->with($this->request, $response, $token);

        $this->loginManager->logInUser('main', $user, $response);
    }
}
