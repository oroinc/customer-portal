<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Menu\ConditionEvaluator;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Menu\ConditionEvaluator\MenuConditionEvaluator;
use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

class MenuConditionEvaluatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $logger;

    /**
     * @var ExpressionLanguage|\PHPUnit\Framework\MockObject\MockObject
     */
    private $expressionLanguage;

    /**
     * @var MenuConditionEvaluator
     */
    private $menuConditionEvaluator;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->expressionLanguage = $this->createMock(ExpressionLanguage::class);
        $this->menuConditionEvaluator = new MenuConditionEvaluator($this->expressionLanguage, $this->logger);
    }

    /**
     * @dataProvider evaluateDataProvider
     *
     * @param string $conditionString
     * @param string $evaluationResult
     * @param bool   $expectedResult
     */
    public function testEvaluate($conditionString, $evaluationResult, $expectedResult)
    {
        $childMenu1 = $this->mockMenuItem($conditionString);

        $this->expressionLanguage
            ->expects(static::once())
            ->method('evaluate')
            ->with($conditionString)
            ->willReturn($evaluationResult);

        $this->logger
            ->expects(static::never())
            ->method('error');

        $actualResult = $this->menuConditionEvaluator->evaluate($childMenu1, []);
        static::assertSame($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public function evaluateDataProvider()
    {
        return [
            'when evaluation result is empty' => [
                'conditionString' => 'sample_condition',
                'evaluationResult' => '',
                'expectedResult' => false,
            ],
            'when evaluation result == 0' => [
                'conditionString' => 'sample_condition',
                'evaluationResult' => '0',
                'expectedResult' => false,
            ],
            'when evaluation result is string' => [
                'conditionString' => 'sample_condition',
                'evaluationResult' => 'false',
                'expectedResult' => true,
            ],
        ];
    }

    public function testEvaluateWhenConditionIsEmpty()
    {
        $childMenu1 = $this->mockMenuItem('');

        $this->expressionLanguage
            ->expects(static::never())
            ->method('evaluate');

        $this->logger
            ->expects(static::never())
            ->method('error');

        $actualResult = $this->menuConditionEvaluator->evaluate($childMenu1, []);
        static::assertTrue($actualResult);
    }

    public function testEvaluateWhenConditionIsInvalid()
    {
        $conditionString = 'invalid_condition';
        $childMenu1 = $this->mockMenuItem($conditionString);

        $error = 'sample error message';
        $this->expressionLanguage
            ->expects(static::once())
            ->method('evaluate')
            ->with($conditionString)
            ->willThrowException(new SyntaxError($error));

        $logMessage =
            'Exception caught while evaluating menu condition expression: sample error message around position 0.';
        $this->logger
            ->expects(static::once())
            ->method('error')
            ->with($logMessage);

        $actualResult = $this->menuConditionEvaluator->evaluate($childMenu1, []);
        static::assertTrue($actualResult);
    }

    /**
     * @param string $conditionString
     *
     * @return ItemInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function mockMenuItem($conditionString)
    {
        $childMenu1 = $this->createMock(ItemInterface::class);
        $childMenu1
            ->expects(static::once())
            ->method('getExtra')
            ->with('condition')
            ->willReturn($conditionString);

        return $childMenu1;
    }
}
