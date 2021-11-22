<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Asset;

use Oro\Bundle\WebsiteBundle\Asset\BasePathResolver;
use Oro\Bundle\WebsiteBundle\Asset\RequestContext;

class RequestContextTest extends \PHPUnit\Framework\TestCase
{
    /** @var BasePathResolver|\PHPUnit\Framework\MockObject\MockObject */
    private $resolver;

    protected function setUp(): void
    {
        $this->resolver = $this->createMock(BasePathResolver::class);
    }

    public function testGetBaseUrlWhenBasePathResolver(): void
    {
        $baseUrl = '/path';
        $expected = '/resolved-path';
        $context = new RequestContext($baseUrl);
        $context->setBasePathResolver($this->resolver);
        $this->resolver->expects(self::once())
            ->method('resolveBasePath')
            ->with($baseUrl)
            ->willReturn($expected);

        self::assertEquals($expected, $context->getBaseUrl());
    }

    public function testGetBaseUrlWhenNoBasePathResolver(): void
    {
        $baseUrl = '/path';
        $context = new RequestContext($baseUrl);

        self::assertEquals($baseUrl, $context->getBaseUrl());
    }

    /**
     * @dataProvider fromUriDataProvider
     */
    public function testFromUri(
        string $uri,
        ?string $host,
        ?string $scheme,
        ?int $httpPort,
        ?int $httpsPort,
        RequestContext $expected
    ): void {
        $args = func_get_args();
        // Pops $expected from the arguments passed to fromUri().
        array_pop($args);

        $context = call_user_func_array([RequestContext::class, 'fromUri'], array_filter($args));

        self::assertEquals($expected, $context);
    }

    public function fromUriDataProvider(): array
    {
        return [
            [
                'uri' => '/',
                'host' => null,
                'scheme' => null,
                'httpPort' => null,
                'httpsPort' => null,
                'expected' => new RequestContext('/'),
            ],
            [
                'uri' => 'http://sample-host/',
                'host' => null,
                'scheme' => null,
                'httpPort' => null,
                'httpsPort' => null,
                'expected' => new RequestContext('/', 'GET', 'sample-host', 'http'),
            ],
            [
                'uri' => 'https://sample-host:444/',
                'host' => null,
                'scheme' => null,
                'httpPort' => null,
                'httpsPort' => null,
                'expected' => new RequestContext('/', 'GET', 'sample-host', 'https', 80, 444),
            ],
            [
                'uri' => '/sample-uri',
                'host' => 'sample-host',
                'scheme' => 'https',
                'httpPort' => 88,
                'httpsPort' => 444,
                'expected' => new RequestContext('/sample-uri', 'GET', 'sample-host', 'https', 88, 444),
            ],
        ];
    }
}
