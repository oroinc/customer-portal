<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\EventListener;

use Oro\Bundle\WebsiteBundle\Asset\RequestContext;
use Oro\Bundle\WebsiteBundle\EventListener\AssetsRequestListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class AssetsRequestListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var RequestContext|\PHPUnit\Framework\MockObject\MockObject */
    private $requestContext;

    /** @var AssetsRequestListener */
    private $listener;

    protected function setUp(): void
    {
        $this->requestContext = $this->createMock(RequestContext::class);

        $this->listener = new AssetsRequestListener($this->requestContext);
    }

    public function testOnKernelRequest(): void
    {
        $locale = 'en';
        $request = new Request();
        $request->setLocale($locale);
        $this->requestContext->expects(self::once())
            ->method('fromRequest')
            ->with($request);
        $this->requestContext->expects(self::once())
            ->method('setParameter')
            ->with('_locale', $locale);
        $this->listener->onKernelRequest(
            new RequestEvent($this->createMock(HttpKernelInterface::class), $request, null)
        );
    }
}
