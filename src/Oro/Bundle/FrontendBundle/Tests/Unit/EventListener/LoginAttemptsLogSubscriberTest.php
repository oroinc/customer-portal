<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\EventListener;

use Oro\Bundle\FrontendBundle\EventListener\LoginAttemptsLogSubscriber;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\UserBundle\EventListener\LoginAttemptsSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class LoginAttemptsLogSubscriberTest extends \PHPUnit\Framework\TestCase
{
    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject  */
    private $frontendHelper;

    /** @var LoginAttemptsSubscriberInterface|\PHPUnit\Framework\MockObject\MockObject  */
    private $innerSubscriber;

    /** @var LoginAttemptsLogSubscriber  */
    private $subscriber;

    protected function setUp()
    {
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->innerSubscriber = $this->createMock(LoginAttemptsSubscriberInterface::class);

        $this->subscriber = new LoginAttemptsLogSubscriber($this->innerSubscriber, $this->frontendHelper);
    }

    public function testGetSubscribedEvents()
    {
        self::assertEquals(
            [
                AuthenticationEvents::AUTHENTICATION_FAILURE => 'onAuthenticationFailure',
                SecurityEvents::INTERACTIVE_LOGIN            => 'onInteractiveLogin',
            ],
            $this->subscriber::getSubscribedEvents()
        );
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
