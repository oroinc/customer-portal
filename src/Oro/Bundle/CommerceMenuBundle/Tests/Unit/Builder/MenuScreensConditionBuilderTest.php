<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Builder;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuItem;
use Oro\Bundle\CommerceMenuBundle\Builder\MenuScreensConditionBuilder;
use Oro\Bundle\FrontendBundle\Provider\ScreensProviderInterface;

class MenuScreensConditionBuilderTest extends \PHPUnit\Framework\TestCase
{
    private const SCREEN_NAME = 'desktop';
    private const SCREEN = [
        'label' => 'Desktop',
        'hidingCssClass' => 'sample-desktop-class',
    ];

    /** @var ScreensProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $screensProvider;

    /** @var MenuScreensConditionBuilder */
    private $builder;

    protected function setUp(): void
    {
        $this->screensProvider = $this->createMock(ScreensProviderInterface::class);

        $this->builder = new MenuScreensConditionBuilder($this->screensProvider);
    }

    /**
     * @dataProvider buildDataProvider
     */
    public function testBuild(?array $screen, array $attributes, array $expectedAttributes)
    {
        $this->screensProvider->expects(self::atLeastOnce())
            ->method('getScreen')
            ->with(self::SCREEN_NAME)
            ->willReturn($screen);

        $mainMenu = $this->getMenuAndChildren($attributes, ['screens' => [self::SCREEN_NAME]]);

        $this->builder->build($mainMenu);

        $expectedMainMenu = $this->getMenuAndChildren($expectedAttributes, ['screens' => [self::SCREEN_NAME]]);
        self::assertEquals($expectedMainMenu, $mainMenu);
    }

    public function buildDataProvider(): array
    {
        return [
            'existing screen, existing attributes' => [
                'screen' => self::SCREEN,
                'attributes' => ['class' => 'sample-existing-class', 'data-sample-attr' => 'sample'],
                'expectedAttributes' => [
                    'class' => 'sample-existing-class sample-desktop-class',
                    'data-sample-attr' => 'sample',
                ]
            ],
            'existing screen, empty attributes' => [
                'screen' => self::SCREEN,
                'attributes' => [],
                'expectedAttributes' => ['class' => 'sample-desktop-class']
            ],
            'existing screen, class already set to screen class' => [
                'screen' => self::SCREEN,
                'attributes' => ['class' => 'sample-desktop-class'],
                'expectedAttributes' => ['class' => 'sample-desktop-class'],
            ],
            'non-existing screen, empty attributes' => [
                'screen' => null,
                'attributes' => [],
                'expectedAttributes' => [],
            ],
            'non-existing screen, existing attributes' => [
                'screen' => null,
                'attributes' => ['class' => 'sample-existing-class', 'data-sample-attr' => 'sample'],
                'expectedAttributes' => ['class' => 'sample-existing-class', 'data-sample-attr' => 'sample'],
            ],
        ];
    }

    /**
     * Ensures that menu is not changed when no screens are found in extras.
     */
    public function testBuildWhenNoScreensInExtras()
    {
        $this->screensProvider->expects(self::never())
            ->method('getScreen');

        $mainMenu = $this->getMenuAndChildren([], []);

        $this->builder->build($mainMenu);

        $expectedMainMenu = $this->getMenuAndChildren([], []);
        self::assertEquals(
            $expectedMainMenu,
            $mainMenu,
            'Menu should not be changed when screens are not set in menu item'
        );
    }

    private function getMenuItem(
        string $menuName,
        array $attributes,
        array $extras,
        array $children,
        bool $isDisplayed
    ): ItemInterface {
        $factory = $this->createMock(FactoryInterface::class);
        $menuItem = new MenuItem($menuName, $factory);
        $menuItem->setAttributes($attributes);
        $menuItem->setExtras($extras);
        $menuItem->setChildren($children);
        $menuItem->setDisplay($isDisplayed);

        return $menuItem;
    }

    private function getMenuAndChildren(array $menuItem2Attributes, array $menuItem2Extras): ItemInterface
    {
        return $this->getMenuItem('main_menu', [], [], [
            'menu_item_1' => $this->getMenuItem('menu_item_1', [], [], [], false),
            'menu_item_2' => $this->getMenuItem('menu_item_2', $menuItem2Attributes, $menuItem2Extras, [], true),
        ], true);
    }
}
