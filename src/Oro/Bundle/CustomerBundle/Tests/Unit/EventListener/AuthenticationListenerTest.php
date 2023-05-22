<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Event\FilterCustomerUserResponseEvent;
use Oro\Bundle\CustomerBundle\EventListener\AuthenticationListener;
use Oro\Bundle\CustomerBundle\Security\LoginManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationListenerTest extends \PHPUnit\Framework\TestCase
{
    private const FIREWALL_NAME = 'test_firewall';

    /** @var LoginManager|\PHPUnit\Framework\MockObject\MockObject */
    private $loginManager;

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var AuthenticationListener */
    private $listener;

    protected function setUp(): void
    {
        $this->loginManager = $this->createMock(LoginManager::class);
        $this->configManager = $this->createMock(ConfigManager::class);

        $this->listener = new AuthenticationListener($this->loginManager, $this->configManager, self::FIREWALL_NAME);
    }

    public function testAuthenticateOnRegistrationCompletedWithoutAutoLoginAndConfimration()
    {
        $customerUser = new CustomerUser();
        $request = new Request();
        $response = new Response();
        $event = new FilterCustomerUserResponseEvent($customerUser, $request, $response);

        $this->configManager->expects(self::once())
            ->method('get')
            ->willReturnMap([
                ['oro_customer.auto_login_after_registration', false, false, null, false]
            ]);

        $this->loginManager->expects(self::never())
            ->method('logInUser');

        $this->listener->authenticate($event);
    }

    public function testAuthenticateOnRegistrationCompleteWithoutRequestAutoLogin()
    {
        $customerUser = new CustomerUser();
        $request = new Request();
        $request->query->set('_oro_customer_auto_login', true);
        $response = new Response();
        $event = new FilterCustomerUserResponseEvent($customerUser, $request, $response);

        $this->configManager->expects(self::never())
            ->method('get');

        $this->loginManager->expects(self::once())
            ->method('logInUser')
            ->with(self::FIREWALL_NAME, $customerUser, $response);

        $this->listener->authenticate($event);
    }

    public function testAuthenticateOnRegistrationCompleteWithAutoLoginAndConfirmation()
    {
        $request = new Request();
        $response = new Response();
        $customerUser = new CustomerUser();
        $event = new FilterCustomerUserResponseEvent($customerUser, $request, $response);

        $this->configManager->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap([
                ['oro_customer.auto_login_after_registration', false, false, null, true],
                ['oro_customer.confirmation_required', false, false, null, true]
            ]);

        $this->loginManager->expects(self::never())
            ->method('logInUser');

        $this->listener->authenticate($event);
    }

    public function testAuthenticateOnRegistrationCompleteWithAutoLoginAndWitoutConfirmation()
    {
        $request = new Request();
        $response = new Response();
        $customerUser = new CustomerUser();
        $event = new FilterCustomerUserResponseEvent($customerUser, $request, $response);

        $this->configManager->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap([
                ['oro_customer.auto_login_after_registration', false, false, null, true],
                ['oro_customer.confirmation_required', false, false, null, false]
            ]);

        $this->loginManager->expects(self::once())
            ->method('logInUser')
            ->with(self::FIREWALL_NAME, $customerUser, $response);

        $this->listener->authenticate($event);
    }

    public function testAuthenticateWithoutAutoLogin()
    {
        $customerUser = new CustomerUser();
        $request = new Request();
        $response = new Response();
        $event = new FilterCustomerUserResponseEvent($customerUser, $request, $response);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.auto_login_after_registration')
            ->willReturn(false);

        $this->loginManager->expects(self::never())
            ->method('logInUser');

        $this->listener->authenticateOnRegistrationConfirmed($event);
    }

    public function testAuthenticateWithoutRequestAutoLogin()
    {
        $customerUser = new CustomerUser();
        $request = new Request();
        $request->query->set('_oro_customer_auto_login', true);
        $response = new Response();
        $event = new FilterCustomerUserResponseEvent($customerUser, $request, $response);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.auto_login_after_registration')
            ->willReturn(false);

        $this->loginManager->expects(self::once())
            ->method('logInUser')
            ->with(self::FIREWALL_NAME, $customerUser, $response);

        $this->listener->authenticateOnRegistrationConfirmed($event);
    }

    public function testAuthenticateWithAutoLogin()
    {
        $request = new Request();
        $response = new Response();
        $customerUser = new CustomerUser();
        $event = new FilterCustomerUserResponseEvent($customerUser, $request, $response);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.auto_login_after_registration')
            ->willReturn(true);

        $this->loginManager->expects(self::once())
            ->method('logInUser')
            ->with(self::FIREWALL_NAME, $customerUser, $response);

        $this->listener->authenticateOnRegistrationConfirmed($event);
    }
}
