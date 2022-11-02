<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Asset;

use Oro\Bundle\WebsiteBundle\Asset\BasePathResolver;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class BasePathResolverTest extends \PHPUnit\Framework\TestCase
{
    /** @var RequestStack|\PHPUnit\Framework\MockObject\MockObject */
    private $requestStack;

    /** @var BasePathResolver */
    private $resolver;

    protected function setUp(): void
    {
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->resolver = new BasePathResolver($this->requestStack);
    }

    public function testResolveBasePathNoMasterRequest(): void
    {
        $this->requestStack->expects(self::once())
            ->method('getMainRequest')
            ->willReturn(null);

        self::assertEquals('/path', $this->resolver->resolveBasePath('/path'));
    }

    public function testResolveBasePath(): void
    {
        $request = $this->createMock(Request::class);
        $request->server = new ParameterBag(['WEBSITE_PATH' => '/path']);

        $this->requestStack->expects(self::atLeastOnce())
            ->method('getMainRequest')
            ->willReturn($request);

        self::assertEquals('/base', $this->resolver->resolveBasePath('/base/path'));
    }

    public function testGetBasePathNoConfiguration(): void
    {
        $request = $this->createMock(Request::class);
        $request->server = new ParameterBag([]);

        $this->requestStack->expects(self::atLeastOnce())
            ->method('getMainRequest')
            ->willReturn($request);

        self::assertEquals('/base/path', $this->resolver->resolveBasePath('/base/path'));
    }
}
