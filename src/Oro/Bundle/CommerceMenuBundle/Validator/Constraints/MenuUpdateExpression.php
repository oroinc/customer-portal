<?php

namespace Oro\Bundle\CommerceMenuBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for validating menu update expression syntax.
 *
 * This constraint ensures that expression strings used in menu updates are valid and can be
 * evaluated by the expression language without syntax errors.
 */
class MenuUpdateExpression extends Constraint
{
}
