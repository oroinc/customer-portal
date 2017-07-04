<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Api\Processor;

use Oro\Bundle\WebsiteBundle\Api\Processor\SetDefaultWebsiteProcessor;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Entity\WebsiteAwareInterface;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\ChainProcessor\ContextInterface;
use PHPUnit\Framework\TestCase;

class SetDefaultWebsiteProcessorTest extends TestCase
{
    /**
     * @var WebsiteManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteManager;

    /**
     * @var SetDefaultWebsiteProcessor
     */
    private $processor;

    protected function setUp()
    {
        $this->websiteManager = $this->createMock(WebsiteManager::class);

        $this->processor = new SetDefaultWebsiteProcessor($this->websiteManager);
    }

    public function testProcessWrongDataType()
    {
        $context = $this->createMock(ContextInterface::class);
        $context
            ->expects(static::once())
            ->method('getResult')
            ->willReturn(null);

        $this->websiteManager
            ->expects(static::never())
            ->method('getDefaultWebsite');

        $this->processor->process($context);
    }

    public function testProcess()
    {
        $context = $this->createMock(ContextInterface::class);
        $context
            ->expects(static::once())
            ->method('getResult')
            ->willReturn($this->createMock(WebsiteAwareInterface::class));

        $this->websiteManager
            ->expects(static::once())
            ->method('getDefaultWebsite')
            ->willReturn(new Website());

        $this->processor->process($context);
    }
}
