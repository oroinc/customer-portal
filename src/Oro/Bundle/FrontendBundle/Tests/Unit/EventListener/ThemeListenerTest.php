<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\EventListener;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\EventListener\ThemeListener;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\NavigationBundle\Event\ResponseHashnavListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ThemeListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|FrontendHelper */
    private $helper;

    /** @var \PHPUnit\Framework\MockObject\MockObject|HttpKernelInterface */
    private $kernel;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ConfigManager */
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
     * @param int     $requestType
     * @param boolean $isFrontendRequest
     * @param string  $expectedLayoutTheme
     * @param boolean $hashNavigation
     * @param boolean $fullRedirect
     *
     * @dataProvider onKernelRequestProvider
     */
    public function testOnKernelRequest(
        $requestType,
        $isFrontendRequest,
        $expectedLayoutTheme,
        $hashNavigation,
        $fullRedirect
    ) {
        $request = new Request();
        if ($hashNavigation) {
            $request->headers->set(ResponseHashnavListener::HASH_NAVIGATION_HEADER, true);
        }
        $event = new GetResponseEvent($this->kernel, $request, $requestType);

        $this->helper->expects($this->any())
            ->method('isFrontendUrl')
            ->with($request->getPathInfo())
            ->willReturn($isFrontendRequest);

        $this->configManager->expects($this->any())
            ->method('get')
            ->with('oro_frontend.frontend_theme')
            ->willReturn('test_layout_theme');

        $this->listener->onKernelRequest($event);

        $this->assertEquals($expectedLayoutTheme, $request->attributes->get('_theme'));
        $this->assertEquals($fullRedirect, $request->attributes->has('_fullRedirect'));
    }

    /**
     * @return array
     */
    public function onKernelRequestProvider()
    {
        return [
            'not master request' => [
                'requestType'         => HttpKernelInterface::SUB_REQUEST,
                'isFrontendRequest'   => true,
                'expectedLayoutTheme' => 'test_layout_theme',
                'hashNavigation'      => false,
                'fullRedirect'        => false
            ],
            'frontend'           => [
                'requestType'         => HttpKernelInterface::MASTER_REQUEST,
                'isFrontendRequest'   => true,
                'expectedLayoutTheme' => 'test_layout_theme',
                'hashNavigation'      => true,
                'fullRedirect'        => true
            ],
            'backend'            => [
                'requestType'         => HttpKernelInterface::MASTER_REQUEST,
                'isFrontendRequest'   => false,
                'expectedLayoutTheme' => null,
                'hashNavigation'      => false,
                'fullRedirect'        => false
            ]
        ];
    }

    /**
     * @dataProvider onKernelViewProvider
     *
     * @param string      $requestType
     * @param bool        $isFrontendRequest
     * @param bool        $hasTheme
     * @param bool|string $deletedAnnotation
     */
    public function testOnKernelView($requestType, $isFrontendRequest, $hasTheme, $deletedAnnotation)
    {
        $request = new Request();
        $request->attributes->set('_template', true);
        $request->attributes->set('_layout', true);
        if ($hasTheme) {
            $request->attributes->set('_theme', 'test');
        }
        $event = new GetResponseForControllerResultEvent($this->kernel, $request, $requestType, []);

        $this->helper->expects($this->any())
            ->method('isFrontendUrl')
            ->with($request->getPathInfo())
            ->willReturn($isFrontendRequest);

        $this->listener->onKernelView($event);

        if ($deletedAnnotation && $requestType === HttpKernelInterface::MASTER_REQUEST) {
            $this->assertFalse($request->attributes->has($deletedAnnotation));
        }
    }

    /**
     * @return array
     */
    public function onKernelViewProvider()
    {
        return [
            'backend sub-request'                       => [
                'requestType'       => HttpKernelInterface::SUB_REQUEST,
                'isFrontendRequest' => false,
                'hasTheme'          => false,
                'deletedAnnotation' => false
            ],
            'backend master request'                    => [
                'requestType'       => HttpKernelInterface::MASTER_REQUEST,
                'isFrontendRequest' => false,
                'hasTheme'          => false,
                'deletedAnnotation' => false
            ],
            'frontend sub-request without layout theme' => [
                'requestType'        => HttpKernelInterface::SUB_REQUEST,
                'isFrontendRequest'  => true,
                'hasTheme'           => false,
                'deletedAnnotations' => '_layout'
            ],
            'frontend master request with layout theme' => [
                'requestType'        => HttpKernelInterface::MASTER_REQUEST,
                'isFrontendRequest'  => true,
                'hasTheme'           => true,
                'deletedAnnotations' => '_template'
            ],
            'frontend sub-request with layout theme'    => [
                'requestType'        => HttpKernelInterface::SUB_REQUEST,
                'isFrontendRequest'  => true,
                'hasTheme'           => true,
                'deletedAnnotations' => '_template'
            ]
        ];
    }
}
