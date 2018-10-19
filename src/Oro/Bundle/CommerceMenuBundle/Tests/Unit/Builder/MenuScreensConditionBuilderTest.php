<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Builder;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuItem;
use Oro\Bundle\CommerceMenuBundle\Builder\MenuScreensConditionBuilder;
use Oro\Bundle\FrontendBundle\Provider\ScreensProviderInterface;

class MenuScreensConditionBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @internal
     */
    const SCREEN_NAME = 'desktop';

    /**
     * @internal
     */
    const SCREEN = [
        'label' => 'Desktop',
        'hidingCssClass' => 'sample-desktop-class',
    ];

    /**
     * @var ScreensProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $screensProvider;

    /**
     * @var MenuScreensConditionBuilder
     */
    private $builder;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->screensProvider = $this->createMock(ScreensProviderInterface::class);
        $this->builder = new MenuScreensConditionBuilder($this->screensProvider);
    }

    /**
     * @dataProvider buildDataProvider
     *
     * @param array|null $screen
     * @param array      $attributes
     * @param array      $expectedAttributes
     */
    public function testBuild($screen, array $attributes, array $expectedAttributes)
    {
        $this->screensProvider
            ->expects(static::atLeastOnce())
            ->method('getScreen')
            ->with(self::SCREEN_NAME)
            ->willReturn($screen);

        $mainMenu = $this->mockMenuAndChildren($attributes, ['screens' => [self::SCREEN_NAME]]);

        $this->builder->build($mainMenu);

        $expectedMainMenu = $this->mockMenuAndChildren($expectedAttributes, ['screens' => [self::SCREEN_NAME]]);
        static::assertEquals($expectedMainMenu, $mainMenu);
    }

    /**
     * @return array
     */
    public function buildDataProvider()
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
        $this->screensProvider
            ->expects(static::never())
            ->method('getScreen');

        $mainMenu = $this->mockMenuAndChildren([], []);

        $this->builder->build($mainMenu);

        $expectedMainMenu = $this->mockMenuAndChildren([], []);
        static::assertEquals(
            $expectedMainMenu,
            $mainMenu,
            'Menu should not be changed when screens are not set in menu item'
        );
    }

    /**
     * @param string $menuName
     * @param array  $attributes
     * @param array  $extras
     * @param array  $children
     * @param bool   $isDisplayed
     *
     * @return ItemInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function mockMenuItem($menuName, array $attributes, array $extras, array $children, $isDisplayed)
    {
        $factory = $this->createMock(FactoryInterface::class);
        $menuItem = new MenuItem($menuName, $factory);
        $menuItem->setAttributes($attributes);
        $menuItem->setExtras($extras);
        $menuItem->setChildren($children);
        $menuItem->setDisplay($isDisplayed);

        return $menuItem;
    }

    /**
     * @param array $menuItem2Attributes
     * @param array $menuItem2Extras
     *
     * @return ItemInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function mockMenuAndChildren(array $menuItem2Attributes, array $menuItem2Extras)
    {
        return $this->mockMenuItem('main_menu', [], [], [
            'menu_item_1' => $this->mockMenuItem('menu_item_1', [], [], [], false),
            'menu_item_2' => $this->mockMenuItem('menu_item_2', $menuItem2Attributes, $menuItem2Extras, [], true),
        ], true);
    }
}
