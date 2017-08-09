<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Builder;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Builder\MenuDisplayBuilder;
use Oro\Bundle\CommerceMenuBundle\Menu\ConditionEvaluator\ConditionEvaluatorInterface;

class MenuDisplayBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MenuDisplayBuilder
     */
    private $builder;

    /**
     * @var ConditionEvaluatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $conditionEvaluator;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->conditionEvaluator = $this->createMock(ConditionEvaluatorInterface::class);
        $this->builder = new MenuDisplayBuilder($this->conditionEvaluator);
    }

    public function testBuild()
    {
        $childMenu1 = $this->createMock(ItemInterface::class);
        $childMenu1
            ->expects(static::once())
            ->method('getChildren')
            ->willReturn([]);

        $childMenu1
            ->expects(static::once())
            ->method('isDisplayed')
            ->willReturn(false);

        $childMenu2 = $this->createMock(ItemInterface::class);
        $childMenu2
            ->expects(static::once())
            ->method('getChildren')
            ->willReturn([]);

        $childMenu2
            ->expects(static::once())
            ->method('isDisplayed')
            ->willReturn(true);

        $childMenu2
            ->expects(static::once())
            ->method('setDisplay')
            ->willReturn(false);

        /** @var $mainMenu ItemInterface|\PHPUnit_Framework_MockObject_MockObject */
        $mainMenu = $this->createMock(ItemInterface::class);
        $mainMenu->expects(static::once())
                 ->method('getChildren')
                 ->willReturn([$childMenu1, $childMenu2]);

        $this->conditionEvaluator
            ->expects(static::exactly(2))
            ->method('evaluate')
            ->willReturnMap([
                [$childMenu2, [], false],
                [$mainMenu, [], true]
            ]);

        $this->builder->build($mainMenu);
    }
}
