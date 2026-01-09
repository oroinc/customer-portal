<?php

namespace Oro\Bundle\CommerceMenuBundle\Validator\Constraints;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for the MenuUpdateExpression constraint.
 *
 * This validator checks that expression strings used in menu updates are syntactically valid
 * by attempting to evaluate them with the expression language. It reports any evaluation errors
 * as constraint violations.
 */
class MenuUpdateExpressionValidator extends ConstraintValidator
{
    /** @var ExpressionLanguage */
    private $expressionLanguage;

    public function __construct(ExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    /**
     *
     * @param MenuUpdateExpression $constraint
     */
    #[\Override]
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
