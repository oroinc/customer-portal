<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v7_1_0_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigEntityValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Enables webhook access for the Customer and CustomerUser entities on existing installations.
 *
 * The value is updated only where webhooks are currently disabled, so an entity an administrator
 * has already enabled is left untouched.
 */
class EnableWebhookAccess implements Migration
{
    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $queries->addPostQuery(new UpdateEntityConfigEntityValueQuery(
            Customer::class,
            'integration',
            'webhook_accessible',
            true,
            false
        ));

        $queries->addPostQuery(new UpdateEntityConfigEntityValueQuery(
            CustomerUser::class,
            'integration',
            'webhook_accessible',
            true,
            false
        ));
    }
}
