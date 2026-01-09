<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Asset;

use Oro\Bundle\WebsiteBundle\Asset\BasePathResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ServerBag;

class BasePathResolverTest extends TestCase
{
    private RequestStack&MockObject $requestStack;
    private BasePathResolver $resolver;

    #[\Override]
    protected function setUp(): void
    {
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->resolver = new BasePathResolver($this->requestStack);
    }

    public function testResolveBasePathNoMainRequest(): void
    {
        $this->requestStack->expects(self::once())
            ->method('getMainRequest')
            ->willReturn(null);

        self::assertEquals('/path', $this->resolver->resolveBasePath('/path'));
    }

    public function testResolveBasePath(): void
    {
        $serverBag = $this->createMock(ServerBag::class);
        $serverBag->expects(self::once())
            ->method('get')
            ->with('WEBSITE_PATH')
            ->willReturn('/path');

        $request = $this->createMock(Request::class);
        $request->server = $serverBag;

        $this->requestStack->expects(self::once())
            ->method('getMainRequest')
            ->willReturn($request);

        self::assertEquals('/base', $this->resolver->resolveBasePath('/base/path'));
    }

    public function testGetBasePathNoConfiguration(): void
    {
        $serverBag = $this->createMock(ServerBag::class);
        $serverBag->expects(self::once())
            ->method('get')
            ->with('WEBSITE_PATH')
            ->willReturn(null);

        $request = $this->createMock(Request::class);
        $request->server = $serverBag;

        $this->requestStack->expects(self::once())
            ->method('getMainRequest')
            ->willReturn($request);

        self::assertEquals('/base/path', $this->resolver->resolveBasePath('/base/path'));
    }
}
