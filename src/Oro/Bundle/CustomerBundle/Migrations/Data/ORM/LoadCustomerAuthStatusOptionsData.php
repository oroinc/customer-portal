<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Data\ORM;

use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\EntityExtendBundle\Migration\Fixture\AbstractEnumFixture;

/**
 * Load customer user enum auth status options data.
 */
class LoadCustomerAuthStatusOptionsData extends AbstractEnumFixture
{
    protected function getData(): array
    {
        return [
            CustomerUserManager::STATUS_ACTIVE => 'Active',
            CustomerUserManager::STATUS_RESET => 'Reset',
        ];
    }

    protected function getDefaultValue(): string
    {
        return CustomerUserManager::STATUS_ACTIVE;
    }

    protected function getEnumCode(): string
    {
        return 'cu_auth_status';
    }
}
