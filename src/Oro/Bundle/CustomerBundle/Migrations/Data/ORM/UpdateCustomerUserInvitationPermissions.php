<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\ORM;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserInvitation;

/**
 * Removes access to customer user invitations from the predefined buyer role.
 */
class UpdateCustomerUserInvitationPermissions extends AbstractMassUpdateCustomerUserRolePermissions
{
    #[\Override]
    protected function getACLData(): array
    {
        return [
            'ROLE_FRONTEND_BUYER' => [
                'entity:' . CustomerUserInvitation::class => []
            ]
        ];
    }
}
