<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\EventListener;

use Knp\Menu\FactoryInterface;
use Knp\Menu\MenuItem;
use Oro\Bundle\CommerceMenuBundle\EventListener\SubFolderMenuUpdatesApplyAfterListener;
use Oro\Bundle\CommerceMenuBundle\Handler\SubFolderUriHandler;
use Oro\Bundle\NavigationBundle\Event\MenuUpdatesApplyAfterEvent;
use Oro\Bundle\NavigationBundle\MenuUpdate\Applier\Model\MenuUpdateApplierContext;
use Oro\Bundle\UIBundle\Tools\UrlHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\UrlHelper as SymfonyUrlHelper;
use Symfony\Component\Routing\RequestContext;

class SubFolderMenuUpdatesApplyAfterListenerTest extends TestCase
{
    private RequestStack|MockObject $requestStack;
    private SubFolderUriHandler $uriHandler;
    private SubFolderMenuUpdatesApplyAfterListener $listener;
    private RequestContext $requestContext;

    protected function setUp(): void
    {
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->requestContext = new RequestContext();
        $urlHelper = new UrlHelper(
            new SymfonyUrlHelper($this->requestStack),
            $this->requestStack,
            $this->requestContext,
        );
        $this->uriHandler = new SubFolderUriHandler($this->requestStack, $urlHelper);
        $this->listener = new SubFolderMenuUpdatesApplyAfterListener($this->uriHandler);
    }

    public function testOnMenuUpdatesApplyAfterEmptySubfolder(): void
    {
        $event = $this->createMock(MenuUpdatesApplyAfterEvent::class);
        $event->expects(self::never())
            ->method('getContext');

        $this->listener->onMenuUpdatesApplyAfter($event);
    }

    /**
     * @dataProvider onMenuUpdatesApplyAfterDataProvider
     */
    public function testOnMenuUpdatesApplyAfter(
        string $subFolder,
        string $baseUrl,
        string $uri,
        string $expectedUri
    ): void {
        $request = Request::create('', server: ['WEBSITE_PATH' => $subFolder]);
        $this->requestStack->expects(self::any())
            ->method('getMainRequest')
            ->willReturn($request);
        $this->requestContext->setBaseUrl($baseUrl);

        $menuItem = new MenuItem('test-name', $this->createMock(FactoryInterface::class));
        $menuItem->setUri($uri);

        $context = $this->createMock(MenuUpdateApplierContext::class);
        $context->expects(self::once())
            ->method('getMenuItemsByName')
            ->willReturn([$menuItem]);

        $event = $this->createMock(MenuUpdatesApplyAfterEvent::class);
        $event->expects(self::once())
            ->method('getContext')
            ->willReturn($context);

        $this->listener->onMenuUpdatesApplyAfter($event);

        self::assertEquals($expectedUri, $menuItem->getUri());
    }

    public function onMenuUpdatesApplyAfterDataProvider(): iterable
    {
        yield 'empty uri' => [
            '/fr', '', '', ''
        ];

        yield 'empty uri and base url' => [
            '/fr', '/fr', '', '/fr/'
        ];

        yield 'empty uri with script filename' => [
            '/fr', '/fr/index.php', '', '/fr/index.php/'
        ];

        yield 'empty uri with dev script filename' => [
            '/fr', '/fr/index_dev.php', '', '/fr/index_dev.php/'
        ];

        yield 'valid uri' => [
            '/fr', '/fr', '/test-page', '/fr/test-page'
        ];

        yield 'valid uri with script filename' => [
            '/fr', '/fr/index.php', '/test-page', '/fr/index.php/test-page'
        ];

        yield 'valid uri with dev script filename' => [
            '/fr', '/fr/index.php', '/test-page', '/fr/index.php/test-page'
        ];

        yield 'url as uri' => [
            '/fr', '', 'http://domain.wip', 'http://domain.wip'
        ];

        yield 'ssl url as uri' => [
            '/fr', '', 'https://domain.wip/test-page', 'https://domain.wip/test-page'
        ];

        yield 'url as uri and base url' => [
            '/fr', '/fr', 'http://domain.wip', 'http://domain.wip'
        ];

        yield 'ssl url as uri and base url' => [
            '/fr', '/fr', 'https://domain.wip/test-page', 'https://domain.wip/test-page'
        ];
    }
}
