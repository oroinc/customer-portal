<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Twig;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\MatcherInterface;
use Oro\Bundle\CommerceMenuBundle\Twig\MenuExtension;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class MenuExtensionTest extends \PHPUnit\Framework\TestCase
{
    use TwigExtensionTestCaseTrait;

    /** @var MatcherInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $matcher;

    /** @var RequestStack|\PHPUnit\Framework\MockObject\MockObject */
    private $requestStack;

    /** @var MenuExtension */
    private $extension;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->matcher = $this->getMockBuilder(MatcherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestStack = $this->getMockBuilder(RequestStack::class)->getMock();

        $container = self::getContainerBuilder()
            ->add('knp_menu.matcher', $this->matcher)
            ->add('request_stack', $this->requestStack)
            ->getContainer($this);

        $this->extension = new MenuExtension($container);
    }

    public function testIsCurrent()
    {
        /** @var ItemInterface|\PHPUnit\Framework\MockObject\MockObject $item */
        $item = $this->createMock(ItemInterface::class);

        $this->matcher->expects($this->once())
            ->method('isCurrent')
            ->with(self::identicalTo($item))
            ->will($this->returnValue(true));

        $this->assertTrue(
            self::callTwigFunction($this->extension, 'oro_commercemenu_is_current', [$item])
        );
    }

    public function testIsAncestor()
    {
        /** @var ItemInterface|\PHPUnit\Framework\MockObject\MockObject $item */
        $item = $this->createMock(ItemInterface::class);

        $this->matcher
            ->expects($this->once())
            ->method('isAncestor')
            ->with(self::identicalTo($item))
            ->will($this->returnValue(true));

        $this->assertTrue(
            self::callTwigFunction($this->extension, 'oro_commercemenu_is_ancestor', [$item])
        );
    }

    /**
     * @dataProvider originalUrlDataProvider
     *
     * @param string $url
     */
    public function testGetUrlOriginal($url)
    {
        $this->requestStack
            ->expects($this->never())
            ->method('getCurrentRequest');

        $this->assertEquals($url, $this->extension->getUrl($url));
    }

    /**
     * @dataProvider preparedUrlDataProvider
     *
     * @param string $url
     * @param string $result
     */
    public function testGetUrlPrepared($url, $result)
    {
        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $request->expects($this->once())
            ->method('getUriForPath')
            ->with($result)
            ->willReturn('http://example.com'. $result);

        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->assertEquals('http://example.com' . $result, $this->extension->getUrl($url));
    }

    /**
     * @return array
     */
    public function originalUrlDataProvider()
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

    /**
     * @return array
     */
    public function preparedUrlDataProvider()
    {
        return [
            'without "/"' => [
                'url' => 'help',
                'result' => '/help'
            ],
            'without "/" and with request param' => [
                'url' => 'help?123',
                'result' => '/help?123'
            ],
            'with "/"' => [
                'url' => '/help?123',
                'result' => '/help?123'
            ],
        ];
    }

    public function testGetUrlWithBaseUrl()
    {
        $passedUrl = '/index.php/contact-us';
        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();

        $request->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn('/index.php');

        $request->expects($this->never())
            ->method('getUriForPath');

        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->assertEquals('/index.php/contact-us', $this->extension->getUrl($passedUrl));
    }
}
