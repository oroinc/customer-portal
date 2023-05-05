<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Layout\PathProvider;

use Oro\Bundle\CommerceMenuBundle\Layout\PathProvider\MenuTemplatePathProvider;
use Oro\Component\Layout\Extension\Theme\Model\Theme;
use Oro\Component\Layout\Extension\Theme\Model\ThemeManager;
use Oro\Component\Layout\LayoutContext;

class MenuTemplatePathProviderTest extends \PHPUnit\Framework\TestCase
{
    private ThemeManager|\PHPUnit\Framework\MockObject\MockObject $themeManager;

    private MenuTemplatePathProvider $provider;

    protected function setUp(): void
    {
        $this->themeManager = $this->createMock(ThemeManager::class);

        $this->provider = new MenuTemplatePathProvider($this->themeManager);
    }

    /**
     * @dataProvider pathsDataProvider
     */
    public function testGetPaths(?string $theme, ?string $menuTemplate, array $expectedResults): void
    {
        $context = new LayoutContext();
        $context->set('theme', $theme);
        $context->set('menu_template', $menuTemplate);

        $this->themeManager
            ->expects(self::any())
            ->method('getThemesHierarchy')
            ->willReturnMap([
                ['base', [new Theme('base')]],
                ['black', [new Theme('base'), new Theme('black', 'base')]],
            ]);

        $this->provider->setContext($context);
        self::assertSame($expectedResults, $this->provider->getPaths([]));
    }

    public function pathsDataProvider(): array
    {
        return [
            [

                'theme' => null,
                'menuTemplate ' => null,
                'expectedResults' => [],
            ],
            [

                'theme' => 'base',
                'menuTemplate' => null,
                'expectedResults' => [],
            ],
            [

                'theme' => 'base',
                'menuTemplate' => 'sample_tree',
                'expectedResults' => [
                    'base/menu_template',
                    'base/menu_template/sample_tree',
                ],
            ],
            [

                'theme' => 'black',
                'menuTemplate' => 'sample_tree',
                'expectedResults' => [
                    'base/menu_template',
                    'base/menu_template/sample_tree',
                    'black/menu_template',
                    'black/menu_template/sample_tree',
                ],
            ],
        ];
    }
}
