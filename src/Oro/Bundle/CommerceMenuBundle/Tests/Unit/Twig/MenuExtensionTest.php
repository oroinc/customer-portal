<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Twig;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\MatcherInterface;
use Oro\Bundle\CommerceMenuBundle\Layout\MenuItemRenderer;
use Oro\Bundle\CommerceMenuBundle\Twig\MenuExtension;
use Oro\Bundle\NavigationBundle\Tests\Unit\Entity\Stub\MenuItemStub;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class MenuExtensionTest extends \PHPUnit\Framework\TestCase
{
    use TwigExtensionTestCaseTrait;

    private MatcherInterface|\PHPUnit\Framework\MockObject\MockObject $matcher;

    private RequestStack|\PHPUnit\Framework\MockObject\MockObject $requestStack;

    private MenuItemRenderer|\PHPUnit\Framework\MockObject\MockObject $menuItemRenderer;

    private MenuExtension $extension;

    protected function setUp(): void
    {
        $this->matcher = $this->createMock(MatcherInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->menuItemRenderer = $this->createMock(MenuItemRenderer::class);

        $container = self::getContainerBuilder()
            ->add('knp_menu.matcher', $this->matcher)
            ->add(RequestStack::class, $this->requestStack)
            ->add('oro_commerce_menu.layout.menu_item_renderer', $this->menuItemRenderer)
            ->getContainer($this);

        $this->extension = new MenuExtension($container);
    }

    public function testIsCurrent(): void
    {
        $item = $this->createMock(ItemInterface::class);

        $this->matcher->expects(self::once())
            ->method('isCurrent')
            ->with(self::identicalTo($item))
            ->willReturn(true);

        self::assertTrue(
            self::callTwigFunction($this->extension, 'oro_commercemenu_is_current', [$item])
        );
    }

    public function testIsAncestor(): void
    {
        $item = $this->createMock(ItemInterface::class);

        $this->matcher->expects(self::once())
            ->method('isAncestor')
            ->with(self::identicalTo($item))
            ->willReturn(true);

        self::assertTrue(
            self::callTwigFunction($this->extension, 'oro_commercemenu_is_ancestor', [$item])
        );
    }

    public function testGetUrlEmptyUrlAndRequest(): void
    {
        self::assertEquals(
            '',
            self::callTwigFunction($this->extension, 'oro_commercemenu_get_url', [null])
        );
    }

    public function testGetUrlEmptyUrl(): void
    {
        $baseUrl = '/index.php';
        $uri = 'http://example.com';

        $request = $this->createMock(Request::class);
        $request->expects(self::once())
            ->method('getBaseUrl')
            ->willReturn($baseUrl);
        $request->expects(self::once())
            ->method('getUriForPath')
            ->with('/')
            ->willReturn($uri);

        $this->requestStack->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        self::assertEquals(
            $uri,
            self::callTwigFunction($this->extension, 'oro_commercemenu_get_url', [null])
        );
    }

    /**
     * @dataProvider originalUrlDataProvider
     */
    public function testGetUrlOriginal(string $url): void
    {
        $this->requestStack->expects(self::never())
            ->method('getCurrentRequest');

        self::assertEquals(
            $url,
            self::callTwigFunction($this->extension, 'oro_commercemenu_get_url', [$url])
        );
    }

    /**
     * @dataProvider preparedUrlDataProvider
     */
    public function testGetUrlPrepared(string $url, string $result): void
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::once())
            ->method('getUriForPath')
            ->with($result)
            ->willReturn('http://example.com' . $result);

        $this->requestStack->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        self::assertEquals(
            'http://example.com' . $result,
            self::callTwigFunction($this->extension, 'oro_commercemenu_get_url', [$url])
        );
    }

    public function originalUrlDataProvider(): array
    {
        return [
            'tel' => ['tel:123'],
            'skype' => ['skype:+123?call'],
            'skype callto' => ['callto://+123'],
            'mailto' => ['mailto:someone@example.com?Subject=Hello%20again'],
            'with default schema' => ['//example.com'],
            'with default schema and path' => ['//example.com/123'],
            'with "http" schema' => ['http://example.com'],
            'with "http" schema and path' => ['http://example.com/123'],
            'with "http" schema and port' => ['http://example.com:80'],
            'with "http" schema, port and path' => ['http://example.com:80/123'],
        ];
    }

    public function preparedUrlDataProvider(): array
    {
        return [
            'without "/"' => [
                'url' => 'help',
                'result' => '/help',
            ],
            'without "/" and with request param' => [
                'url' => 'help?123',
                'result' => '/help?123',
            ],
            'with "/"' => [
                'url' => '/help?123',
                'result' => '/help?123',
            ],
        ];
    }

    public function testGetUrlWithBaseUrl(): void
    {
        $passedUrl = '/index.php/contact-us';
        $request = $this->createMock(Request::class);

        $request->expects(self::once())
            ->method('getBaseUrl')
            ->willReturn('/index.php');

        $request->expects(self::never())
            ->method('getUriForPath');

        $this->requestStack->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        self::assertEquals(
            '/index.php/contact-us',
            self::callTwigFunction($this->extension, 'oro_commercemenu_get_url', [$passedUrl])
        );
    }

    public function testRenderMenuItem(): void
    {
        $result = 'sample result';
        $menuItem = new MenuItemStub();
        $this->menuItemRenderer
            ->expects(self::once())
            ->method('render')
            ->with($menuItem)
            ->willReturn($result);

        self::assertEquals(
            $result,
            self::callTwigFunction($this->extension, 'oro_commercemenu_render_menu_item', [$menuItem])
        );
    }
}
