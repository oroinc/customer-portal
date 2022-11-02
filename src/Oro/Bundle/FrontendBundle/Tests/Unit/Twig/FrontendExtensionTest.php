<?php

namespace Oro\Bundle\FrontendBundle\Tests\Unit\Twig;

use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\FrontendBundle\Twig\FrontendExtension;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class FrontendExtensionTest extends \PHPUnit\Framework\TestCase
{
    use TwigExtensionTestCaseTrait;

    /** @var Environment|\PHPUnit\Framework\MockObject\MockObject */
    private $environment;

    /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $frontendHelper;

    /** @var RouterInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $router;

    /** @var FrontendExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->environment = $this->createMock(Environment::class);
        $this->frontendHelper = $this->createMock(FrontendHelper::class);
        $this->router = $this->createMock(RouterInterface::class);

        $container = self::getContainerBuilder()
            ->add(FrontendHelper::class, $this->frontendHelper)
            ->add(RouterInterface::class, $this->router)
            ->getContainer($this);

        $this->extension = new FrontendExtension($container);
    }

    /**
     * @dataProvider getDefaultPageDataProvider
     */
    public function testGetDefaultPage(bool $isFrontendRequest, string $routeName): void
    {
        $this->frontendHelper->expects($this->once())
            ->method('isFrontendRequest')
            ->willReturn($isFrontendRequest);

        $this->router->expects($this->once())
            ->method('generate')
            ->with($routeName)
            ->willReturn($url = 'http://sample-app/sample-url');

        $this->assertEquals($url, self::callTwigFunction($this->extension, 'oro_default_page', [$this->environment]));
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
}
