<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\EventListener;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\EventListener\ThemeListener;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\NavigationBundle\Event\ResponseHashnavListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ThemeListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $helper;

    /** @var HttpKernelInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $kernel;

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var ThemeListener */
    private $listener;

    protected function setUp(): void
    {
        $this->helper = $this->createMock(FrontendHelper::class);
        $this->kernel = $this->createMock(HttpKernelInterface::class);
        $this->configManager = $this->createMock(ConfigManager::class);

        $this->listener = new ThemeListener($this->helper, $this->configManager);
    }

    /**
     * @dataProvider onKernelRequestProvider
     */
    public function testOnKernelRequest(
        int $requestType,
        bool $isFrontendRequest,
        ?string $expectedLayoutTheme,
        bool $hashNavigation,
        bool $fullRedirect
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
            ->willReturn('test_layout_theme');

        $this->listener->onKernelRequest($event);

        self::assertEquals($expectedLayoutTheme, $request->attributes->get('_theme'));
        self::assertEquals($fullRedirect, $request->attributes->has('_fullRedirect'));
    }

    public function onKernelRequestProvider(): array
    {
        return [
            'not master request' => [
                'requestType' => HttpKernelInterface::SUB_REQUEST,
                'isFrontendRequest' => true,
                'expectedLayoutTheme' => 'test_layout_theme',
                'hashNavigation' => false,
                'fullRedirect' => false,
            ],
            'frontend' => [
                'requestType' => HttpKernelInterface::MASTER_REQUEST,
                'isFrontendRequest' => true,
                'expectedLayoutTheme' => 'test_layout_theme',
                'hashNavigation' => true,
                'fullRedirect' => true,
            ],
            'backend' => [
                'requestType' => HttpKernelInterface::MASTER_REQUEST,
                'isFrontendRequest' => false,
                'expectedLayoutTheme' => null,
                'hashNavigation' => false,
                'fullRedirect' => false,
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

        if ($deletedAnnotation && $requestType === HttpKernelInterface::MASTER_REQUEST) {
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
            'backend master request' => [
                'requestType' => HttpKernelInterface::MASTER_REQUEST,
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
            'frontend master request with layout theme' => [
                'requestType' => HttpKernelInterface::MASTER_REQUEST,
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
