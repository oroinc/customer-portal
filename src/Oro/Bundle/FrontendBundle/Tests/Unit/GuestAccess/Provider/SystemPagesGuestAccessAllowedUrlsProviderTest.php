<?php

declare(strict_types=1);

namespace Oro\Bundle\FrontendBundle\Tests\Unit\GuestAccess\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\DependencyInjection\Configuration;
use Oro\Bundle\FrontendBundle\GuestAccess\Provider\SystemPagesGuestAccessAllowedUrlsProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;

class SystemPagesGuestAccessAllowedUrlsProviderTest extends TestCase
{
    private ConfigManager&MockObject $configManager;
    private RouterInterface&MockObject $router;
    private SystemPagesGuestAccessAllowedUrlsProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->router = $this->createMock(RouterInterface::class);

        $this->provider = new SystemPagesGuestAccessAllowedUrlsProvider(
            $this->configManager,
            $this->router
        );
    }

    public function testGetAllowedUrlsPatternsReturnsEmptyArrayWhenNoSystemPagesConfigured(): void
    {
        $this->configManager->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKeyByName(Configuration::GUEST_ACCESS_ALLOWED_SYSTEM_PAGES))
            ->willReturn([]);

        $patterns = $this->provider->getAllowedUrlsPatterns();
        self::assertSame([], $patterns);
    }

    public function testGetAllowedUrlsPatternsWithSystemPages(): void
    {
        $this->configManager->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKeyByName(Configuration::GUEST_ACCESS_ALLOWED_SYSTEM_PAGES))
            ->willReturn(['oro_product_frontend_product_index']);

        $this->router->expects(self::once())
            ->method('generate')
            ->with('oro_product_frontend_product_index', [], RouterInterface::ABSOLUTE_PATH)
            ->willReturn('/products');

        $patterns = $this->provider->getAllowedUrlsPatterns();
        self::assertCount(1, $patterns);
        self::assertSame('^/products$', $patterns[0]);
    }

    public function testGetAllowedUrlsPatternsSkipsNonExistentRoute(): void
    {
        $this->configManager->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKeyByName(Configuration::GUEST_ACCESS_ALLOWED_SYSTEM_PAGES))
            ->willReturn(['non_existent_route']);

        $this->router->expects(self::once())
            ->method('generate')
            ->with('non_existent_route')
            ->willThrowException(new RouteNotFoundException());

        self::assertSame([], $this->provider->getAllowedUrlsPatterns());
    }

    public function testGetAllowedUrlsPatternsWithMultipleSystemPages(): void
    {
        $this->configManager->expects(self::once())
            ->method('get')
            ->with(Configuration::getConfigKeyByName(Configuration::GUEST_ACCESS_ALLOWED_SYSTEM_PAGES))
            ->willReturn(['oro_product_frontend_product_index', 'oro_customer_frontend_customer_user_register']);

        $this->router->expects(self::exactly(2))
            ->method('generate')
            ->willReturnMap([
                [
                    'oro_product_frontend_product_index',
                    [],
                    RouterInterface::ABSOLUTE_PATH,
                    '/products'
                ],
                [
                    'oro_customer_frontend_customer_user_register',
                    [],
                    RouterInterface::ABSOLUTE_PATH,
                    '/customer/user/registration'
                ],
            ]);

        $patterns = $this->provider->getAllowedUrlsPatterns();
        self::assertCount(2, $patterns);
        self::assertSame('^/products$', $patterns[0]);
        self::assertSame('^/customer/user/registration$', $patterns[1]);
    }
}
