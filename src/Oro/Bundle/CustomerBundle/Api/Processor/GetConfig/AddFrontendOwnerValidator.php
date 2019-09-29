<?php

namespace Oro\Bundle\CustomerBundle\Api\Processor\GetConfig;

use Oro\Bundle\CustomerBundle\Validator\Constraints\FrontendOwner;
use Oro\Bundle\OrganizationBundle\Api\Processor\GetConfig\AddOwnerValidator;

/**
 * Adds the validation constraint that is used to validate that
 * a frontend owner of the entity can be changed.
 */
class AddFrontendOwnerValidator extends AddOwnerValidator
{
    /**
     * {@inheritdoc}
     */
    protected function getOwnerConstraintClass()
    {
        return FrontendOwner::class;
    }
}
