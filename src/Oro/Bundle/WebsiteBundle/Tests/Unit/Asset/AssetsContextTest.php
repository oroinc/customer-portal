<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Asset;

use Oro\Bundle\WebsiteBundle\Asset\AssetsContext;
use Oro\Bundle\WebsiteBundle\Asset\BasePathResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class AssetsContextTest extends TestCase
{
    private RequestStack&MockObject $requestStack;
    private BasePathResolver&MockObject $resolver;
    private AssetsContext $context;

    #[\Override]
    protected function setUp(): void
    {
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->resolver = $this->createMock(BasePathResolver::class);

        $this->context = new AssetsContext($this->requestStack);
        $this->context->setBasePathResolver($this->resolver);
    }

    public function testGetBasePath(): void
    {
        $path = '/path';
        $expected = '/resolved-path';
        $request = $this->createMock(Request::class);
        $request->expects(self::atLeastOnce())
            ->method('getBasePath')
            ->willReturn($path);

        $this->requestStack->expects(self::atLeastOnce())
            ->method('getMainRequest')
            ->willReturn($request);

        $this->resolver->expects(self::once())
            ->method('resolveBasePath')
            ->with($path)
            ->willReturn($expected);

        self::assertEquals($expected, $this->context->getBasePath());
    }
}
