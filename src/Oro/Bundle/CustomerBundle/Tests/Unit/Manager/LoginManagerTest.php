<?php

namespace Oro\Bundle\CustomerBundle\Tests\Manager;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Manager\LoginManager;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessor;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LoginManager
     */
    private $loginManager;

    /**
     * @var TokenAccessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tokenAccessor;

    /**
     * @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->tokenAccessor = $this->createMock(TokenAccessor::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->loginManager = new LoginManager($this->tokenAccessor, $this->container);
    }

    public function testLoginUser()
    {
        $firewallName = 'firewall_name';
        $customerUser = new CustomerUser();
        $organization = new Organization();
        $customerUser->setOrganization($organization);

        $token = new UsernamePasswordOrganizationToken(
            $customerUser,
            null,
            $firewallName,
            $organization,
            $customerUser->getRoles()
        );

        $this->tokenAccessor->expects($this->once())
            ->method('setToken')
            ->with($token);


        $this->container->expects($this->at(0))
            ->method('get')
            ->with('request')
            ->willReturn(new Request());

        /** @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject $eventDispatcher */
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->container->expects($this->at(1))
            ->method('get')
            ->with('event_dispatcher')
            ->willReturn($eventDispatcher);

        $event = new InteractiveLoginEvent(new Request(), $token);

        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with('security.interactive_login', $event);

        $this->loginManager->logInUser($firewallName, $customerUser);
    }
}
