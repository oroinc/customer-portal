<?php

namespace Oro\Bundle\CustomerBundle\Validator\Constraints;

use Oro\Bundle\OrganizationBundle\Validator\Constraints\Owner;

/**
 * The constraint that can be used to validate that the current logged in customer user
 * is granted to change the frontend owner for an entity.
 * @Annotation
 */
class FrontendOwner extends Owner
{
    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'frontend_owner_validator';
    }
}
