<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Builder;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Builder\MenuConditionBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

class MenuConditionBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var ExpressionLanguage|\PHPUnit_Framework_MockObject_MockObject
     */
    private $expressionLanguage;

    /**
     * @var MenuConditionBuilder
     */
    private $builder;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->expressionLanguage = $this->createMock(ExpressionLanguage::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->builder = new MenuConditionBuilder($this->expressionLanguage, $this->logger);
    }

    public function testBuild()
    {
        $childMenu1 = $this->mockMenuItem([], null, false);
        $childMenu2 = $this->mockMenuItem([], 'is_logged_in', true);
        $mainMenu = $this->mockMenuItem([$childMenu1, $childMenu2], null, true);

        $this->expressionLanguage
            ->expects(static::once())
            ->method('evaluate')
            ->with('is_logged_in')
            ->willReturn(false);

        $childMenu2
            ->expects(static::once())
            ->method('setDisplay')
            ->willReturn(false);

        $this->logger
            ->expects(static::never())
            ->method('error');

        $this->builder->build($mainMenu);
    }

    public function testBuildWhenConditionInvalid()
    {
        $condition = 'invalid condition';
        $childMenu1 = $this->mockMenuItem([], $condition, true);
        $mainMenu = $this->mockMenuItem([$childMenu1], null, true);

        $error = 'sample error message';
        $this->expressionLanguage
            ->expects(static::once())
            ->method('evaluate')
            ->with($condition)
            ->willThrowException(new SyntaxError($error));

        $logMessage =
            'Exception caught while evaluating menu condition expression: sample error message around position 0.';
        $this->logger
            ->expects(static::once())
            ->method('error')
            ->with($logMessage);

        $this->builder->build($mainMenu);
    }

    /**
     * @param array       $children
     * @param string|null $condition
     * @param bool        $isDisplayed
     *
     * @return ItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockMenuItem(array $children, $condition, $isDisplayed)
    {
        $menuItem = $this->createMock(ItemInterface::class);
        $menuItem
            ->expects(static::once())
            ->method('getChildren')
            ->willReturn($children);

        $menuItem
            ->expects(static::once())
            ->method('isDisplayed')
            ->willReturn($isDisplayed);

        $menuItem
            ->expects(static::any())
            ->method('getExtra')
            ->with(MenuConditionBuilder::CONDITION_KEY)
            ->willReturn($condition);

        return $menuItem;
    }
}
