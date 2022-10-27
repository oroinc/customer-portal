<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Oro\Bundle\CustomerBundle\EventListener\CustomerUserLoginAttemptsLogListener;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\UserBundle\Security\LoginAttemptsHandlerInterface;
use Oro\Component\Testing\Unit\TestContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class CustomerUserLoginAttemptsLogListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var LoginAttemptsHandlerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $handler;

    /** @var LoginAttemptsHandlerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendHandler;

    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendHelper;

    /** @var CustomerUserLoginAttemptsLogListener */
    private $listener;

    protected function setUp(): void
    {
        $this->handler = $this->createMock(LoginAttemptsHandlerInterface::class);
        $this->frontendHandler = $this->createMock(LoginAttemptsHandlerInterface::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);

        $container = TestContainerBuilder::create()
            ->add('handler', $this->handler)
            ->add('frontend_handler', $this->frontendHandler)
            ->getContainer($this);

        $this->listener = new CustomerUserLoginAttemptsLogListener($container, $this->frontendHelper);
    }

    public function testGetSubscribedServices(): void
    {
        self::assertEquals(
            [
                'handler'          => LoginAttemptsHandlerInterface::class,
                'frontend_handler' => LoginAttemptsHandlerInterface::class
            ],
            CustomerUserLoginAttemptsLogListener::getSubscribedServices()
        );
    }

    public function testOnInteractiveLoginForBackendRequest(): void
    {
        $event = new InteractiveLoginEvent(
            $this->createMock(Request::class),
            $this->createMock(TokenInterface::class)
        );

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->handler->expects(self::once())
            ->method('onInteractiveLogin')
            ->with(self::identicalTo($event));
        $this->frontendHandler->expects(self::never())
            ->method(self::anything());

        $this->listener->onInteractiveLogin($event);
    }

    public function testOnInteractiveLoginForFrontendRequest(): void
    {
        $event = new InteractiveLoginEvent(
            $this->createMock(Request::class),
            $this->createMock(TokenInterface::class)
        );

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->frontendHandler->expects(self::once())
            ->method('onInteractiveLogin')
            ->with(self::identicalTo($event));
        $this->handler->expects(self::never())
            ->method(self::anything());

        $this->listener->onInteractiveLogin($event);
    }

    public function testOnAuthenticationFailureForBackendRequest(): void
    {
        $event = new AuthenticationFailureEvent(
            $this->createMock(UsernamePasswordToken::class),
            $this->createMock(AuthenticationException::class)
        );

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->handler->expects(self::once())
            ->method('onAuthenticationFailure')
            ->with(self::identicalTo($event));
        $this->frontendHandler->expects(self::never())
            ->method(self::anything());

        $this->listener->onAuthenticationFailure($event);
    }

    public function testOnAuthenticationFailureForFrontendRequest(): void
    {
        $event = new AuthenticationFailureEvent(
            $this->createMock(UsernamePasswordToken::class),
            $this->createMock(AuthenticationException::class)
        );

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->frontendHandler->expects(self::once())
            ->method('onAuthenticationFailure')
            ->with(self::identicalTo($event));
        $this->handler->expects(self::never())
            ->method(self::anything());

        $this->listener->onAuthenticationFailure($event);
    }
}
