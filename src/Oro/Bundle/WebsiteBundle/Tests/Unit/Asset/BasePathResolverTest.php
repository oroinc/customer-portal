<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Asset;

use Oro\Bundle\WebsiteBundle\Asset\BasePathResolver;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class BasePathResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RequestStack|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestStack;

    /**
     * @var BasePathResolver
     */
    private $resolver;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->resolver = new BasePathResolver($this->requestStack);
    }

    public function testResolveBasePathNoMasterRequest()
    {
        $this->requestStack->expects($this->once())
            ->method('getMasterRequest')
            ->willReturn(null);

        $this->assertEquals('/path', $this->resolver->resolveBasePath('/path'));
    }

    public function testResolveBasePath()
    {
        /** @var Request|\PHPUnit\Framework\MockObject\MockObject $request */
        $request = $this->createMock(Request::class);
        $request->server = new ParameterBag(['WEBSITE_PATH' => '/path']);

        $this->requestStack->expects($this->atLeastOnce())
            ->method('getMasterRequest')
            ->willReturn($request);

        $this->assertEquals('/base', $this->resolver->resolveBasePath('/base/path'));
    }

    public function testGetBasePathNoConfiguration()
    {
        /** @var Request|\PHPUnit\Framework\MockObject\MockObject $request */
        $request = $this->createMock(Request::class);
        $request->server = new ParameterBag([]);

        $this->requestStack->expects($this->atLeastOnce())
            ->method('getMasterRequest')
            ->willReturn($request);

        $this->assertEquals('/base/path', $this->resolver->resolveBasePath('/base/path'));
    }
}
