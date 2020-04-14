<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Asset;

use Oro\Bundle\WebsiteBundle\Asset\BasePathResolver;
use Oro\Bundle\WebsiteBundle\Asset\RequestContext;

class RequestContextTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BasePathResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resolver;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->resolver = $this->createMock(BasePathResolver::class);
    }

    public function testGetBaseUrl()
    {
        $path = '/path';
        $expected = '/resolved-path';
        $context = new RequestContext($path);
        $context->setBasePathResolver($this->resolver);
        $this->resolver->expects($this->once())
            ->method('resolveBasePath')
            ->with($path)
            ->willReturn($expected);

        self::assertEquals($expected, $context->getBaseUrl());
    }
}
