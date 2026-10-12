<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\ORM;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserInvitation;
use Oro\Bundle\EntityExtendBundle\Migration\Fixture\AbstractEnumFixture;

/**
 * Loads customer user invitation status enum options.
 */
class LoadCustomerUserInvitationStatusOptionsData extends AbstractEnumFixture
{
    #[\Override]
    protected function getData(): array
    {
        return [
            CustomerUserInvitation::STATUS_PENDING => 'Pending',
            CustomerUserInvitation::STATUS_ACCEPTED => 'Accepted',
            CustomerUserInvitation::STATUS_REVOKED => 'Revoked',
        ];
    }

    #[\Override]
    protected function getEnumCode(): string
    {
        return CustomerUserInvitation::INTERNAL_STATUS_CODE;
    }

    #[\Override]
    protected function getDefaultValue(): string
    {
        return CustomerUserInvitation::STATUS_PENDING;
    }
}
