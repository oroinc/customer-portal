<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Security\Firewall;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Security\Firewall\CustomerVisitorCookieFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;

class CustomerVisitorCookieFactoryTest extends TestCase
{
    private ConfigManager&MockObject $configManager;

    #[\Override]
    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_customer.customer_visitor_cookie_lifetime_days')
            ->willReturn(30);
    }

    public function testGetCookie(): void
    {
        $factory = new CustomerVisitorCookieFactory('auto', true, $this->configManager, Cookie::SAMESITE_NONE);
        $cookie = $factory->getCookie('test_session_id', 123);

        self::assertFalse($cookie->isSecure());
        self::assertTrue($cookie->isHttpOnly());
        self::assertEquals(
            base64_encode(json_encode('test_session_id', JSON_THROW_ON_ERROR)),
            $cookie->getValue()
        );
    }

    public function testGetCookieOnSecuredConfig(): void
    {
        $factory = new CustomerVisitorCookieFactory(true, true, $this->configManager, Cookie::SAMESITE_NONE);
        $cookie = $factory->getCookie('test_session_id', 123);

        self::assertTrue($cookie->isSecure());
    }

    public function testGetCookieOnNonSecuredConfig(): void
    {
        $factory = new CustomerVisitorCookieFactory(false, true, $this->configManager, Cookie::SAMESITE_NONE);
        $cookie = $factory->getCookie('test_session_id', 123);

        self::assertFalse($cookie->isSecure());
    }

    public function testGetCookieOnNonHttpOnlyConfig(): void
    {
        $factory = new CustomerVisitorCookieFactory('auto', false, $this->configManager, Cookie::SAMESITE_NONE);
        $cookie = $factory->getCookie('test_session_id', 123);

        self::assertFalse($cookie->isHttpOnly());
    }

    public function testCookieWithSameSiteValue(): void
    {
        $factory = new CustomerVisitorCookieFactory('auto', false, $this->configManager, Cookie::SAMESITE_STRICT);
        $cookie = $factory->getCookie('test_session_id', 123);

        self::assertEquals(Cookie::SAMESITE_STRICT, $cookie->getSameSite());
    }
}
