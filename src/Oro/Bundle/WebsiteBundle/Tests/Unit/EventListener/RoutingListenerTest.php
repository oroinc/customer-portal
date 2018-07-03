<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\EventListener;

use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\EventListener\RoutingListener;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RoutingListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RoutingListener
     */
    private $listener;

    /**
     * @var WebsiteManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $websiteManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->websiteManager = $this->getMockBuilder(WebsiteManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new RoutingListener(
            $this->websiteManager
        );
    }

    public function testWebsiteWasAddedToRequest()
    {
        $website = new Website();
        $this->websiteManager->method('getCurrentWebsite')->willReturn($website);
        $request = Request::create('https://orocommerce.com/product');
        /** @var GetResponseEvent|\PHPUnit\Framework\MockObject\MockObject $event */
        $event = $this->getMockBuilder(GetResponseEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('getRequest')->willReturn($request);
        $this->listener->onRequest($event);
        $this->assertSame($website, $request->attributes->get('current_website'));
    }
}
