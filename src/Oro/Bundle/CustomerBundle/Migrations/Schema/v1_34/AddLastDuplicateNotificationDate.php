<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_34;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Add last_duplicate_notification_date column to oro_customer_user table.
 */
class AddLastDuplicateNotificationDate implements Migration
{
    #[\Override]
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_customer_user');
        if (!$table->hasColumn('last_duplicate_notification_date')) {
            $table->addColumn('last_duplicate_notification_date', 'datetime', ['notnull' => false]);
        }
    }
}
