<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Twig;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\FrontendBundle\Twig\FrontendExtension;
use Oro\Bundle\UIBundle\ContentProvider\ContentProviderManager;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class FrontendExtensionTest extends TestCase
{
    use TwigExtensionTestCaseTrait;

    private Environment|MockObject $environment;

    private FrontendHelper|MockObject $frontendHelper;

    private RouterInterface|MockObject $router;

    private ContentProviderManager|MockObject $contentProviderManager;

    private ContentProviderManager|MockObject $frontendContentProviderManager;

    private FrontendExtension $extension;

    protected function setUp(): void
    {
        $this->environment = $this->createMock(Environment::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->contentProviderManager = $this->createMock(ContentProviderManager::class);
        $this->frontendContentProviderManager = $this->createMock(ContentProviderManager::class);

        $container = self::getContainerBuilder()
            ->add(FrontendHelper::class, $this->frontendHelper)
            ->add(RouterInterface::class, $this->router)
            ->add('oro_ui.content_provider.manager', $this->contentProviderManager)
            ->add('oro_frontend.content_provider.manager', $this->frontendContentProviderManager)
            ->getContainer($this);

        $this->extension = new FrontendExtension($container);
    }

    /**
     * @dataProvider getDefaultPageDataProvider
     */
    public function testGetDefaultPage(bool $isFrontendRequest, string $routeName): void
    {
        $this->frontendHelper->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn($isFrontendRequest);

        $this->router->expects(self::once())
            ->method('generate')
            ->with($routeName)
            ->willReturn($url = 'http://sample-app/sample-url');

        self::assertEquals($url, self::callTwigFunction($this->extension, 'oro_default_page', [$this->environment]));
    }

    public function getDefaultPageDataProvider(): array
    {
        return [
            [
                'isFrontendRequest' => false,
                'routeName' => 'oro_default',
            ],
            [
                'isFrontendRequest' => true,
                'routeName' => 'oro_frontend_root',
            ],
        ];
    }

    /**
     * @dataProvider contentDataProvider
     */
    public function testGetContentWhenFrontendRequest(
        array $content,
        ?array $additionalContent,
        ?array $keys,
        array $expected
    ): void {
        $this->frontendHelper
            ->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->contentProviderManager
            ->expects(self::never())
            ->method(self::anything());

        $this->frontendContentProviderManager
            ->expects(self::once())
            ->method('getContent')
            ->with($keys)
            ->willReturn($content);

        self::assertEquals(
            $expected,
            self::callTwigFunction($this->extension, 'oro_get_content', [$additionalContent, $keys])
        );
    }

    /**
     * @dataProvider contentDataProvider
     */
    public function testGetContentWhenNotFrontendRequest(
        array $content,
        ?array $additionalContent,
        ?array $keys,
        array $expected
    ): void {
        $this->frontendHelper
            ->expects(self::once())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->frontendContentProviderManager
            ->expects(self::never())
            ->method(self::anything());

        $this->contentProviderManager
            ->expects(self::once())
            ->method('getContent')
            ->with($keys)
            ->willReturn($content);

        self::assertEquals(
            $expected,
            self::callTwigFunction($this->extension, 'oro_get_content', [$additionalContent, $keys])
        );
    }

    public function contentDataProvider(): array
    {
        return [
            'with additional content and keys' => [
                'content' => ['b' => 'c'],
                'additionalContent' => ['a' => 'b'],
                'keys' => ['a', 'b', 'c'],
                'expected' => ['a' => 'b', 'b' => 'c'],
            ],
            'without additional content and keys' => [
                'content' => ['b' => 'c'],
                'additionalContent' => null,
                'keys' => null,
                'expected' => ['b' => 'c'],
            ],
        ];
    }
}
