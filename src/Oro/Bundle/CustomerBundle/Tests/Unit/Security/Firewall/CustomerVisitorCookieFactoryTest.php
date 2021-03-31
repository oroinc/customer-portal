<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security\Firewall;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Security\Firewall\CustomerVisitorCookieFactory;
use Symfony\Component\HttpFoundation\Cookie;

class CustomerVisitorCookieFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_customer.customer_visitor_cookie_lifetime_days')
            ->willReturn(30);
    }

    public function testGetCookie()
    {
        $factory = new CustomerVisitorCookieFactory('auto', true, $this->configManager, Cookie::SAMESITE_NONE);
        $cookie = $factory->getCookie('test_visitor', 'test_session_id');

        self::assertFalse($cookie->isSecure());
        self::assertTrue($cookie->isHttpOnly());
        self::assertEquals(base64_encode(json_encode(['test_visitor', 'test_session_id'])), $cookie->getValue());
    }

    public function testGetCookieOnSecuredConfig()
    {
        $factory = new CustomerVisitorCookieFactory(true, true, $this->configManager, Cookie::SAMESITE_NONE);
        $cookie = $factory->getCookie('test_visitor', 'test_session_id');

        self::assertTrue($cookie->isSecure());
    }

    public function testGetCookieOnNonSecuredConfig()
    {
        $factory = new CustomerVisitorCookieFactory(false, true, $this->configManager, Cookie::SAMESITE_NONE);
        $cookie = $factory->getCookie('test_visitor', 'test_session_id');

        self::assertFalse($cookie->isSecure());
    }

    public function testGetCookieOnNonHttpOnlyConfig()
    {
        $factory = new CustomerVisitorCookieFactory('auto', false, $this->configManager, Cookie::SAMESITE_NONE);
        $cookie = $factory->getCookie('test_visitor', 'test_session_id');

        self::assertFalse($cookie->isHttpOnly());
    }

    public function testCookieWithSamesiteValue()
    {
        $factory = new CustomerVisitorCookieFactory('auto', false, $this->configManager, Cookie::SAMESITE_STRICT);
        $cookie = $factory->getCookie('test_visitor', 'test_session_id');

        self::assertEquals(Cookie::SAMESITE_STRICT, $cookie->getSameSite());
    }
}
