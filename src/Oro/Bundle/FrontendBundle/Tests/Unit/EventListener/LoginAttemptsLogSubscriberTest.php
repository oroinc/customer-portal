<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\EventListener;

use Oro\Bundle\FrontendBundle\EventListener\LoginAttemptsLogHandler;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\UserBundle\EventListener\LoginAttemptsHandlerInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginAttemptsLogSubscriberTest extends \PHPUnit\Framework\TestCase
{
    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendHelper;

    /** @var LoginAttemptsHandlerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $innerSubscriber;

    /** @var LoginAttemptsLogHandler */
    private $subscriber;

    protected function setUp(): void
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->innerSubscriber = $this->createMock(LoginAttemptsHandlerInterface::class);

        $this->subscriber = new LoginAttemptsLogHandler($this->innerSubscriber, $this->frontendHelper);
    }

    public function testOnAuthenticationFailureOnNotFrontendRequest()
    {
        $event = $this->createMock(AuthenticationFailureEvent::class);

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->innerSubscriber->expects(self::once())
            ->method('onAuthenticationFailure')
            ->with($event);

        $this->subscriber->onAuthenticationFailure($event);
    }

    public function testOnAuthenticationFailureOnFrontendRequest()
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->innerSubscriber->expects(self::never())
            ->method('onAuthenticationFailure');

        $this->subscriber->onAuthenticationFailure($this->createMock(AuthenticationFailureEvent::class));
    }

    public function testOnInteractiveLoginOnNotFrontendRequest()
    {
        $event = $this->createMock(InteractiveLoginEvent::class);

        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->innerSubscriber->expects(self::once())
            ->method('onInteractiveLogin')
            ->with($event);

        $this->subscriber->onInteractiveLogin($event);
    }

    public function testOnInteractiveLoginOnFrontendRequest()
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->innerSubscriber->expects(self::never())
            ->method('onInteractiveLogin');

        $this->subscriber->onInteractiveLogin($this->createMock(InteractiveLoginEvent::class));
    }
}
