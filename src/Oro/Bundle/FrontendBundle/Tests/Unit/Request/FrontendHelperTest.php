<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Request;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class FrontendHelperTest extends \PHPUnit\Framework\TestCase
{
    const BACKEND_PREFIX = '/admin';

    /**
     * @param string $path
     * @param bool $isFrontend
     * @dataProvider isFrontendRequestDataProvider
     */
    public function testIsFrontendRequest($path, $isFrontend)
    {
        $request = Request::create($path) ;

        /** @var RequestStack|\PHPUnit\Framework\MockObject\MockObject $requestStack */
        $requestStack = $this->createMock('Symfony\Component\HttpFoundation\RequestStack');
        $requestStack->expects($this->once())->method('getMasterRequest')->willReturn($request);

        /** @var ContainerInterface|\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())
            ->method('get')
            ->with('request_stack')
            ->willReturn($requestStack);

        $container->expects($this->any())
            ->method('getParameter')
            ->with('installed')
            ->willReturn(true);

        $helper = new FrontendHelper(self::BACKEND_PREFIX, $container);
        $this->assertSame($isFrontend, $helper->isFrontendRequest());
    }

    /**
     * @return array
     */
    public function isFrontendRequestDataProvider()
    {
        return [
            'backend' => [
                'path' => self::BACKEND_PREFIX . '/backend',
                'isFrontend' => false,
            ],
            'frontend' => [
                'path' => '/frontend',
                'isFrontend' => true,
            ],
            'frontend with backend part' => [
                'path' => '/frontend' . self::BACKEND_PREFIX,
                'isFrontend' => true,
            ],
            'frontend with backend part and slug' => [
                'path' => '/frontend' . self::BACKEND_PREFIX . '/slug',
                'isFrontend' => true,
            ],
        ];
    }

    public function testIsFrontendRequestWithoutPath()
    {
        /** @var RequestStack|\PHPUnit\Framework\MockObject\MockObject $requestStack */
        $requestStack = $this->createMock('Symfony\Component\HttpFoundation\RequestStack');
        $requestStack->expects($this->once())->method('getMasterRequest')->willReturn(null);

        /** @var ContainerInterface|\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())
            ->method('get')
            ->with('request_stack')
            ->willReturn($requestStack);

        $container->expects($this->never())
            ->method('getParameter')
            ->with('installed');

        $helper = new FrontendHelper(self::BACKEND_PREFIX, $container);
        $this->assertFalse($helper->isFrontendRequest());
    }

    public function testIsFrontendRequestNotInstalled()
    {
        /** @var RequestStack|\PHPUnit\Framework\MockObject\MockObject $requestStack */
        $requestStack = $this->createMock('Symfony\Component\HttpFoundation\RequestStack');
        $requestStack->expects($this->never())
            ->method('getMasterRequest');

        /** @var ContainerInterface|\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())
            ->method('get')
            ->with('request_stack')
            ->willReturn($requestStack);

        $container->expects($this->once())
            ->method('getParameter')
            ->with('installed')
            ->willReturn(false);

        $helper = new FrontendHelper(self::BACKEND_PREFIX, $container);
        $this->assertFalse($helper->isFrontendRequest(new Request([], [], ['_route' => 'test'])));
    }

    public function testIsFrontendUrlForNotInstalled()
    {
        /** @var ContainerInterface|\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->createMock(ContainerInterface::class);

        $container->expects($this->once())
            ->method('getParameter')
            ->with('installed')
            ->willReturn(false);

        $helper = new FrontendHelper(self::BACKEND_PREFIX, $container);
        $this->assertFalse($helper->isFrontendUrl('/test'));
    }

    public function testIsFrontendUrlForBackendUrl()
    {
        /** @var ContainerInterface|\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->createMock(ContainerInterface::class);

        $container->expects($this->once())
            ->method('getParameter')
            ->with('installed')
            ->willReturn(true);

        $helper = new FrontendHelper(self::BACKEND_PREFIX, $container);
        $this->assertFalse($helper->isFrontendUrl(self::BACKEND_PREFIX . '/test'));
    }

    public function testIsFrontendUrl()
    {
        /** @var ContainerInterface|\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->createMock(ContainerInterface::class);

        $container->expects($this->once())
            ->method('getParameter')
            ->with('installed')
            ->willReturn(true);

        $helper = new FrontendHelper(self::BACKEND_PREFIX, $container);
        $this->assertTrue($helper->isFrontendUrl('/test'));
    }
}
