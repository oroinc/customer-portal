<?php

namespace Oro\Bundle\CommerceMenuBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class MenuUpdateExpression extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'oro_commerce_menu_update_expression_validator';
    }
}
