<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Builder;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Builder\MenuDisplayBuilder;
use Oro\Bundle\CommerceMenuBundle\Menu\ConditionEvaluator\ConditionEvaluatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MenuDisplayBuilderTest extends TestCase
{
    private ConditionEvaluatorInterface&MockObject $conditionEvaluator;
    private MenuDisplayBuilder $builder;

    #[\Override]
    protected function setUp(): void
    {
        $this->conditionEvaluator = $this->createMock(ConditionEvaluatorInterface::class);
        $this->builder = new MenuDisplayBuilder($this->conditionEvaluator);
    }

    public function testBuild(): void
    {
        $childMenu1 = $this->createMock(ItemInterface::class);
        $childMenu1->expects(self::once())
            ->method('getChildren')
            ->willReturn([]);
        $childMenu1->expects(self::once())
            ->method('isDisplayed')
            ->willReturn(false);

        $childMenu2 = $this->createMock(ItemInterface::class);
        $childMenu2->expects(self::once())
            ->method('getChildren')
            ->willReturn([]);
        $childMenu2->expects(self::once())
            ->method('isDisplayed')
            ->willReturn(true);
        $childMenu2->expects(self::once())
            ->method('setDisplay')
            ->willReturn($childMenu2);

        $mainMenu = $this->createMock(ItemInterface::class);
        $mainMenu->expects(self::once())
            ->method('getChildren')
            ->willReturn([$childMenu1, $childMenu2]);

        $this->conditionEvaluator->expects(self::once())
            ->method('evaluate')
            ->willReturnMap([
                [$childMenu2, [], false],
                [$mainMenu, [], true]
            ]);

        $this->builder->build($mainMenu);
    }
}
