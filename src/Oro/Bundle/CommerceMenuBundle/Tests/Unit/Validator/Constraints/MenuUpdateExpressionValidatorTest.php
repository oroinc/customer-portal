<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\CommerceMenuBundle\Validator\Constraints\MenuUpdateExpression;
use Oro\Bundle\CommerceMenuBundle\Validator\Constraints\MenuUpdateExpressionValidator;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class MenuUpdateExpressionValidatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var ExpressionLanguage|\PHPUnit\Framework\MockObject\MockObject */
    protected $expressionLanguage;

    /** @var MenuUpdateExpressionValidator */
    private $validator;

    protected function setUp(): void
    {
        $this->expressionLanguage = new ExpressionLanguage();
        $this->validator = new MenuUpdateExpressionValidator($this->expressionLanguage);
    }

    /**
     * @dataProvider expressionsProvider
     *
     * @param string $expression
     * @param string $message
     * @param bool $valid
     */
    public function testValidation($expression, $message, $valid)
    {
        /** @var ExecutionContextInterface|\PHPUnit\Framework\MockObject\MockObject $context */
        $context = $this->createMock(ExecutionContextInterface::class);

        if ($valid) {
            $context->expects($this->never())->method('addViolation');
        } else {
            $context->expects($this->once())
                ->method('addViolation')
                ->with($message);
        }

        /** @var MenuUpdateExpression|\PHPUnit\Framework\MockObject\MockObject $constraint */
        $constraint = $this->createMock(MenuUpdateExpression::class);

        $this->validator->initialize($context);

        $this->validator->validate($expression, $constraint);
    }

    /**
     * @return array
     */
    public function expressionsProvider()
    {
        return [
            'valid' => [
                'true',
                '',
                true
            ],
            'non valid 1' => [
                '=true',
                'Unexpected character "=" around position 0 for expression `=true`.',
                false
            ],
            'non valid 2' => [
                'some()',
                'The function "some" does not exist around position 1 for expression `some()`.',
                false
            ],
            'non valid 3' => [
                '1 + var',
                'Variable "var" is not valid around position 5 for expression `1 + var`.',
                false
            ],
        ];
    }
}
