<?php

namespace Oro\Bundle\CommerceMenuBundle\Validator\Constraints;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class MenuUpdateExpressionValidator extends ConstraintValidator
{
    /** @var ExpressionLanguage */
    private $expressionLanguage;

    public function __construct(ExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    /**
     * {@inheritdoc}
     *
     * @param MenuUpdateExpression $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value) {
            try {
                $this->expressionLanguage->evaluate($value);
            } catch (\Exception $ex) {
                $this->context->addViolation($ex->getMessage());
            }
        }
    }
}
