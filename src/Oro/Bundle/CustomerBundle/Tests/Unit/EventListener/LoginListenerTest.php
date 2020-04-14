<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\EventListener;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\EventListener\LoginListener;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListenerTest extends \PHPUnit\Framework\TestCase
{
    const TEST_URL = 'http://test_url/';

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|TokenInterface
     */
    protected $token;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|InteractiveLoginEvent
     */
    protected $event;

    /**
     * @var LoginListener
     */
    protected $listener;

    protected function setUp(): void
    {
        $this->request = Request::create(self::TEST_URL);

        $this->token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $this->event = $this->getMockBuilder('Symfony\Component\Security\Http\Event\InteractiveLoginEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new LoginListener();
    }

    protected function tearDown(): void
    {
        unset($this->request, $this->token, $this->event, $this->listener);
    }

    /**
     * @dataProvider dataProvider
     *
     * @param UserInterface $user
     * @param bool $expected
     */
    public function testOnSuccessLogin(UserInterface $user, $expected)
    {
        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->event->expects($this->once())
            ->method('getAuthenticationToken')
            ->willReturn($this->token);
        $this->event->expects($expected ? $this->once() : $this->never())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->assertNull($this->request->attributes->get('_fullRedirect'));

        $this->listener->onSecurityInteractiveLogin($this->event);

        $this->assertEquals($expected, $this->request->attributes->get('_fullRedirect'));
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            'backend user' => [
                'user' => new User(),
                'expected' => null,
            ],
            'customer user' => [
                'user' => new CustomerUser(),
                'expected' => true,
            ],
        ];
    }
}
