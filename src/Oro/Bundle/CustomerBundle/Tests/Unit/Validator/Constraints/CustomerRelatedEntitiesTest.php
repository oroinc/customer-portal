<?php

namespace Oro\Bundle\CustomerBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\CustomerBundle\Validator\Constraints\CustomerRelatedEntities;
use Oro\Bundle\CustomerBundle\Validator\Constraints\CustomerRelatedEntitiesValidator;
use Symfony\Component\Validator\Constraint;

class CustomerRelatedEntitiesTest extends \PHPUnit\Framework\TestCase
{
    public function testValidatedBy()
    {
        $constraint = new CustomerRelatedEntities();

        self::assertEquals(CustomerRelatedEntitiesValidator::class, $constraint->validatedBy());
    }

    public function testGetTargets()
    {
        $constraint = new CustomerRelatedEntities();

        self::assertEquals(Constraint::CLASS_CONSTRAINT, $constraint->getTargets());
    }
}
