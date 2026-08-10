<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Form\Autocomplete;

use Oro\Bundle\CustomerBundle\Form\Autocomplete\FrontendSearchRegistry;
use Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface;
use Oro\Bundle\FormBundle\Autocomplete\SearchRegistryInterface;
use Oro\Bundle\FormBundle\Exception\NotFoundSearchHandlerException;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Component\Testing\Unit\TestContainerBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FrontendSearchRegistryTest extends TestCase
{
    private SearchRegistryInterface&MockObject $innerRegistry;
    private FrontendHelper&MockObject $frontendHelper;
    private SearchHandlerInterface&MockObject $frontendSearchHandler;
    private SearchHandlerInterface&MockObject $innerSearchHandler;
    private FrontendSearchRegistry $searchRegistry;

    #[\Override]
    protected function setUp(): void
    {
        $this->innerRegistry = $this->createMock(SearchRegistryInterface::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->frontendSearchHandler = $this->createMock(SearchHandlerInterface::class);
        $this->innerSearchHandler = $this->createMock(SearchHandlerInterface::class);

        $container = TestContainerBuilder::create()
            ->add('frontend_handler', $this->frontendSearchHandler)
            ->getContainer($this);

        $this->searchRegistry = new FrontendSearchRegistry($this->innerRegistry, $this->frontendHelper);
        $this->searchRegistry->setFrontendSearchHandlers($container);
    }

    public function testGetAndHasSearchHandlerForFrontendRequest(): void
    {
        $this->frontendHelper->expects(self::exactly(3))
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->innerRegistry->expects(self::never())
            ->method(self::anything());

        self::assertTrue($this->searchRegistry->hasSearchHandler('frontend_handler'));
        self::assertFalse($this->searchRegistry->hasSearchHandler('unknown_handler'));
        self::assertSame($this->frontendSearchHandler, $this->searchRegistry->getSearchHandler('frontend_handler'));
    }

    public function testGetSearchHandlerFailsForFrontendRequest(): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->innerRegistry->expects(self::never())
            ->method(self::anything());

        $this->expectException(NotFoundSearchHandlerException::class);
        $this->expectExceptionMessage('Search handler "unknown_handler" is not registered.');

        $this->searchRegistry->getSearchHandler('unknown_handler');
    }

    public function testGetAndHasSearchHandlerForBackendRequest(): void
    {
        $this->frontendHelper->expects(self::exactly(2))
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->innerRegistry->expects(self::once())
            ->method('hasSearchHandler')
            ->with('inner_handler')
            ->willReturn(true);
        $this->innerRegistry->expects(self::once())
            ->method('getSearchHandler')
            ->with('inner_handler')
            ->willReturn($this->innerSearchHandler);

        self::assertTrue($this->searchRegistry->hasSearchHandler('inner_handler'));
        self::assertSame($this->innerSearchHandler, $this->searchRegistry->getSearchHandler('inner_handler'));
    }
}
