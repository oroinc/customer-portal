<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\EventListener;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\EventListener\ThemeListener;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\NavigationBundle\Event\ResponseHashnavListener;
use Oro\Bundle\ThemeBundle\Provider\ThemeConfigurationProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ThemeListenerTest extends TestCase
{
    private FrontendHelper|MockObject $helper;

    private HttpKernelInterface|MockObject $kernel;

    private ConfigManager|MockObject $configManager;

    private ThemeConfigurationProvider|MockObject $themeConfigurationProvider;

    private ThemeListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->helper = $this->createMock(FrontendHelper::class);
        $this->kernel = $this->createMock(HttpKernelInterface::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->themeConfigurationProvider = $this->createMock(ThemeConfigurationProvider::class);

        $this->listener = new ThemeListener($this->helper, $this->configManager, $this->themeConfigurationProvider);
    }

    /**
     * @dataProvider onKernelRequestProvider
     */
    public function testOnKernelRequest(
        int $requestType,
        bool $isFrontendRequest,
        ?string $expectedLayoutTheme,
        bool $hashNavigation,
        bool $fullRedirect,
        ?string $theme
    ): void {
        $request = new Request();
        if ($hashNavigation) {
            $request->headers->set(ResponseHashnavListener::HASH_NAVIGATION_HEADER, true);
        }
        $event = new RequestEvent($this->kernel, $request, $requestType);

        $this->helper->expects(self::any())
            ->method('isFrontendUrl')
            ->with($request->getPathInfo())
            ->willReturn($isFrontendRequest);

        $this->configManager->expects(self::any())
            ->method('get')
            ->with('oro_frontend.frontend_theme')
            ->willReturn('default');

        $this->themeConfigurationProvider->expects(self::any())
            ->method('getThemeName')
            ->willReturn($theme);

        $this->listener->onKernelRequest($event);

        self::assertEquals($expectedLayoutTheme, $request->attributes->get('_theme'));
        self::assertEquals($fullRedirect, $request->attributes->has('_fullRedirect'));
    }

    public function onKernelRequestProvider(): array
    {
        return [
            'not main request' => [
                'requestType' => HttpKernelInterface::SUB_REQUEST,
                'isFrontendRequest' => true,
                'expectedLayoutTheme' => 'test_layout_theme',
                'hashNavigation' => false,
                'fullRedirect' => false,
                'theme' => 'test_layout_theme',
            ],
            'frontend (no theme configuration value)' => [
                'requestType' => HttpKernelInterface::MAIN_REQUEST,
                'isFrontendRequest' => true,
                'expectedLayoutTheme' => 'default',
                'hashNavigation' => true,
                'fullRedirect' => true,
                'theme' => null,
            ],
            'frontend' => [
                'requestType' => HttpKernelInterface::MAIN_REQUEST,
                'isFrontendRequest' => true,
                'expectedLayoutTheme' => 'test_layout_theme',
                'hashNavigation' => true,
                'fullRedirect' => true,
                'theme' => 'test_layout_theme',
            ],
            'backend' => [
                'requestType' => HttpKernelInterface::MAIN_REQUEST,
                'isFrontendRequest' => false,
                'expectedLayoutTheme' => null,
                'hashNavigation' => false,
                'fullRedirect' => false,
                'theme' => null,
            ],
        ];
    }

    /**
     * @dataProvider onKernelViewProvider
     */
    public function testOnKernelView(
        int $requestType,
        bool $isFrontendRequest,
        bool $hasTheme,
        string|bool $deletedAnnotation
    ): void {
        $request = new Request();
        $request->attributes->set('_template', true);
        $request->attributes->set('_layout', true);
        if ($hasTheme) {
            $request->attributes->set('_theme', 'test');
        }
        $event = new ViewEvent($this->kernel, $request, $requestType, []);

        $this->helper->expects(self::any())
            ->method('isFrontendUrl')
            ->with($request->getPathInfo())
            ->willReturn($isFrontendRequest);

        $this->listener->onKernelView($event);

        if ($deletedAnnotation && $requestType === HttpKernelInterface::MAIN_REQUEST) {
            self::assertFalse($request->attributes->has($deletedAnnotation));
        }
    }

    public function onKernelViewProvider(): array
    {
        return [
            'backend sub-request' => [
                'requestType' => HttpKernelInterface::SUB_REQUEST,
                'isFrontendRequest' => false,
                'hasTheme' => false,
                'deletedAnnotation' => false,
            ],
            'backend main request' => [
                'requestType' => HttpKernelInterface::MAIN_REQUEST,
                'isFrontendRequest' => false,
                'hasTheme' => false,
                'deletedAnnotation' => false,
            ],
            'frontend sub-request without layout theme' => [
                'requestType' => HttpKernelInterface::SUB_REQUEST,
                'isFrontendRequest' => true,
                'hasTheme' => false,
                'deletedAnnotations' => '_layout',
            ],
            'frontend main request with layout theme' => [
                'requestType' => HttpKernelInterface::MAIN_REQUEST,
                'isFrontendRequest' => true,
                'hasTheme' => true,
                'deletedAnnotations' => '_template',
            ],
            'frontend sub-request with layout theme' => [
                'requestType' => HttpKernelInterface::SUB_REQUEST,
                'isFrontendRequest' => true,
                'hasTheme' => true,
                'deletedAnnotations' => '_template',
            ],
        ];
    }
}
