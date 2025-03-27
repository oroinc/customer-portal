<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * The constraint that can be used to validate that a customer user assigned to an entity
 * belongs to a customer assigned to this entity.
 * @Annotation
 */
#[\Attribute]
class CustomerOwner extends Constraint
{
    public string $message = 'oro.customer.message.owner';

    #[\Override]
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
