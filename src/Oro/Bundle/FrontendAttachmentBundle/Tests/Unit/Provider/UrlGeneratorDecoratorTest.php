<?php

namespace Oro\Bundle\FrontendAttachmentBundle\Tests\Unit\Provider;

use Oro\Bundle\FrontendAttachmentBundle\Provider\UrlGeneratorDecorator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

class UrlGeneratorDecoratorTest extends \PHPUnit\Framework\TestCase
{
    /** @var UrlGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $urlGenerator;

    /** @var UrlGeneratorDecorator */
    private $decorator;

    protected function setUp(): void
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $this->decorator = new UrlGeneratorDecorator($this->urlGenerator);
    }

    public function testSetContext(): void
    {
        $context = $this->createMock(RequestContext::class);

        $this->urlGenerator->expects($this->once())
            ->method('setContext')
            ->with($context);

        $this->decorator->setContext($context);
    }

    public function testGetContext(): void
    {
        $context = $this->createMock(RequestContext::class);

        $this->urlGenerator->expects($this->once())
            ->method('getContext')
            ->willReturn($context);

        $this->assertSame($context, $this->decorator->getContext());
    }

    public function testGenerate(): void
    {
        $route = 'test';
        $parameters = ['id' => 1];

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with($route, $parameters)
            ->willReturn('/test/1');

        $this->assertSame('/test/1', $this->decorator->generate($route, $parameters));
    }

    public function testGenerateNoExceptionWhenDeprecatedRoute(): void
    {
        $route = '_oro_frontend_attachment_filter_image';
        $parameters = ['id' => 1];

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with($route, $parameters)
            ->willReturn('/test/1');

        $this->assertSame('/test/1', $this->decorator->generate($route, $parameters));
    }
}
