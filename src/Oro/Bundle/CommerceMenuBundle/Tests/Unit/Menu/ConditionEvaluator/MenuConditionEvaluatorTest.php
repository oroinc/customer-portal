<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Menu\ConditionEvaluator;

use Knp\Menu\ItemInterface;
use Oro\Bundle\CommerceMenuBundle\Menu\ConditionEvaluator\MenuConditionEvaluator;
use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

class MenuConditionEvaluatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var ExpressionLanguage|\PHPUnit\Framework\MockObject\MockObject */
    private $expressionLanguage;

    /** @var MenuConditionEvaluator */
    private $menuConditionEvaluator;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->expressionLanguage = $this->createMock(ExpressionLanguage::class);

        $this->menuConditionEvaluator = new MenuConditionEvaluator($this->expressionLanguage, $this->logger);
    }

    /**
     * @dataProvider evaluateDataProvider
     */
    public function testEvaluate(string $conditionString, string $evaluationResult, bool $expectedResult)
    {
        $childMenu1 = $this->getMenuItem($conditionString);

        $this->expressionLanguage->expects(self::once())
            ->method('evaluate')
            ->with($conditionString)
            ->willReturn($evaluationResult);

        $this->logger->expects(self::never())
            ->method('error');

        $actualResult = $this->menuConditionEvaluator->evaluate($childMenu1, []);
        self::assertSame($expectedResult, $actualResult);
    }

    public function evaluateDataProvider(): array
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
        $childMenu1 = $this->getMenuItem('');

        $this->expressionLanguage->expects(self::never())
            ->method('evaluate');

        $this->logger->expects(self::never())
            ->method('error');

        $actualResult = $this->menuConditionEvaluator->evaluate($childMenu1, []);
        self::assertTrue($actualResult);
    }

    public function testEvaluateWhenConditionIsInvalid()
    {
        $conditionString = 'invalid_condition';
        $childMenu1 = $this->getMenuItem($conditionString);

        $error = 'sample error message';
        $this->expressionLanguage->expects(self::once())
            ->method('evaluate')
            ->with($conditionString)
            ->willThrowException(new SyntaxError($error));

        $logMessage =
            'Exception caught while evaluating menu condition expression: sample error message around position 0.';
        $this->logger->expects(self::once())
            ->method('error')
            ->with($logMessage);

        $actualResult = $this->menuConditionEvaluator->evaluate($childMenu1, []);
        self::assertTrue($actualResult);
    }

    private function getMenuItem(string $conditionString): ItemInterface
    {
        $childMenu1 = $this->createMock(ItemInterface::class);
        $childMenu1->expects(self::once())
            ->method('getExtra')
            ->with('condition')
            ->willReturn($conditionString);

        return $childMenu1;
    }
}
