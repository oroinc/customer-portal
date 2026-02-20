<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Validate that a customer user assigned to an entity
 * belongs to a customer assigned to this entity.
 */
class CustomerOwner extends Constraint
{
    public string $message = 'oro.customer.message.owner';

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
