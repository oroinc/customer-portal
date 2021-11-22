<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Security\LoginManager;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationTokenFactoryInterface;
use Oro\Bundle\SecurityBundle\Model\Role;
use Oro\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
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

    /** @var EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $eventDispatcher;

    /** @var LoginManager */
    private $loginManager;

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
        $this->rememberMe = $this->createMock(RememberMeServicesInterface::class);

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
                null,
                'main',
                $this->isInstanceOf(Organization::class),
                $roles
            )
            ->willReturn($token);

        $this->rememberMe->expects(self::once())
            ->method('loginSuccess')
            ->with($this->request, $response, $token);

        $this->loginManager->logInUser('main', $user, $response);
    }
}
