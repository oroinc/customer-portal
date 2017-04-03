<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Builder;

use Knp\Menu\ItemInterface;

use Oro\Bundle\CommerceMenuBundle\Builder\MenuConditionBuilder;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class MenuConditionBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var MenuConditionBuilder */
    private $builder;

    /** @var ExpressionLanguage|\PHPUnit_Framework_MockObject_MockObject */
    private $expressionLanguage;

    protected function setUp()
    {
        $this->expressionLanguage = $this->getMockBuilder(ExpressionLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new MenuConditionBuilder($this->expressionLanguage);
    }

    public function testBuild()
    {
        /** @var $childMenu1 ItemInterface|\PHPUnit_Framework_MockObject_MockObject */
        $childMenu1 = $this->createMock(ItemInterface::class);
        $childMenu1->expects($this->once())
            ->method('getChildren')
            ->willReturn([]);

        $childMenu1->expects($this->exactly(2))
            ->method('getExtra')
            ->withConsecutive(
                [MenuConditionBuilder::CONDITION_KEY],
                [MenuConditionBuilder::IS_ALLOWED_OPTION_KEY]
            )
            ->willReturnOnConsecutiveCalls('is_logged_in', false);

        /** @var $childMenu2 ItemInterface|\PHPUnit_Framework_MockObject_MockObject */
        $childMenu2 = $this->createMock(ItemInterface::class);
        $childMenu2->expects($this->once())
            ->method('getChildren')
            ->willReturn([]);

        $childMenu2->expects($this->exactly(3))
            ->method('getExtra')
            ->withConsecutive(
                [MenuConditionBuilder::CONDITION_KEY],
                [MenuConditionBuilder::IS_ALLOWED_OPTION_KEY],
                [MenuConditionBuilder::CONDITION_KEY]
            )
            ->willReturnOnConsecutiveCalls('is_logged_in', true, 'is_logged_in');

        $this->expressionLanguage->expects($this->once())
            ->method('evaluate')
            ->with('is_logged_in')
            ->willReturn(false);

        $childMenu2->expects($this->once())
            ->method('setExtra')
            ->with(MenuConditionBuilder::IS_ALLOWED_OPTION_KEY, false);

        /** @var $mainMenu ItemInterface|\PHPUnit_Framework_MockObject_MockObject */
        $mainMenu = $this->createMock(ItemInterface::class);
        $mainMenu->expects($this->once())
            ->method('getChildren')
            ->willReturn([$childMenu1, $childMenu2]);

        $mainMenu->expects($this->once())
            ->method('getExtra')
            ->with(MenuConditionBuilder::CONDITION_KEY)
            ->willReturn(null);

        $this->builder->build($mainMenu);
    }
}
