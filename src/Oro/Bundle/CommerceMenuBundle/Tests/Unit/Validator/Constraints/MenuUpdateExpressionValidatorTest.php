<?php

namespace Oro\Bundle\CommerceMenuBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\CommerceMenuBundle\Validator\Constraints\MenuUpdateExpression;
use Oro\Bundle\CommerceMenuBundle\Validator\Constraints\MenuUpdateExpressionValidator;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class MenuUpdateExpressionValidatorTest extends ConstraintValidatorTestCase
{
    /** @var ExpressionLanguage|\PHPUnit\Framework\MockObject\MockObject */
    private $expressionLanguage;

    protected function setUp(): void
    {
        $this->expressionLanguage = new ExpressionLanguage();
        parent::setUp();
    }

    protected function createValidator()
    {
        return new MenuUpdateExpressionValidator($this->expressionLanguage);
    }

    /**
     * @dataProvider expressionsProvider
     */
    public function testValidation(string $expression, string $message, bool $valid)
    {
        $constraint = new MenuUpdateExpression();
        $this->validator->validate($expression, $constraint);

        if ($valid) {
            $this->assertNoViolation();
        } else {
            $this->buildViolation($message)
                ->assertRaised();
        }
    }

    public function expressionsProvider(): array
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
