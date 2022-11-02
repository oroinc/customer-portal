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
    private const TEST_URL = 'http://test_url/';

    private Request $request;

    private TokenInterface|\PHPUnit\Framework\MockObject\MockObject $token;

    private LoginListener $listener;

    protected function setUp(): void
    {
        $this->request = Request::create(self::TEST_URL);
        $this->token = $this->createMock(TokenInterface::class);

        $this->listener = new LoginListener();
    }

    /**
     * @dataProvider dataProvider
     *
     * @param UserInterface $user
     * @param bool $expected
     */
    public function testOnSuccessLogin(UserInterface $user, $expected): void
    {
        $this->token->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        self::assertNull($this->request->attributes->get('_fullRedirect'));

        $this->listener->onSecurityInteractiveLogin(new InteractiveLoginEvent($this->request, $this->token));

        self::assertEquals($expected, $this->request->attributes->get('_fullRedirect'));
    }

    public function dataProvider(): array
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
