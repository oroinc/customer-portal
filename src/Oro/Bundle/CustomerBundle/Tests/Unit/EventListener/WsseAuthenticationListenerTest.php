<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\EventListener\WsseAuthenticationListener;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Security\UserLoginAttemptLogger;
use Oro\Bundle\WsseAuthenticationBundle\Security\WsseToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class WsseAuthenticationListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var UserLoginAttemptLogger|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var WsseAuthenticationListener */
    private $listener;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(UserLoginAttemptLogger::class);
        $this->listener = new WsseAuthenticationListener($this->logger);
    }

    public function testOnAuthenticationSuccessWithNotSupportedToken(): void
    {
        $token = new UsernamePasswordToken('test', 'main');
        $event = new AuthenticationEvent($token);

        $this->logger->expects(self::never())
            ->method('logSuccessLoginAttempt');

        $this->listener->onAuthenticationSuccess($event);
    }

    public function testOnAuthenticationSuccessWithBackendUserInToken(): void
    {
        $token = new WsseToken(new User(), 'main', 'test');
        $event = new AuthenticationEvent($token);

        $this->logger->expects(self::never())
            ->method('logSuccessLoginAttempt');

        $this->listener->onAuthenticationSuccess($event);
    }

    public function testOnAuthenticationSuccess(): void
    {
        $customerUser = new CustomerUser();
        $token = new WsseToken($customerUser, 'main', 'test');
        $event = new AuthenticationEvent($token);

        $this->logger->expects(self::once())
            ->method('logSuccessLoginAttempt')
            ->with($customerUser, 'wsse');

        $this->listener->onAuthenticationSuccess($event);
    }
}
