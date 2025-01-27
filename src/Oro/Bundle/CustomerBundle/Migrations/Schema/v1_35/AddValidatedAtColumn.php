<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_35;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Add validated_at column to address tables.
 */
class AddValidatedAtColumn implements Migration
{
    public function up(Schema $schema, QueryBag $queries): void
    {
        $table = $schema->getTable('oro_customer_address');

        if (!$table->hasColumn('validated_at')) {
            $table->addColumn('validated_at', 'datetime', ['notnull' => false]);
        }

        $table = $schema->getTable('oro_customer_user_address');

        if (!$table->hasColumn('validated_at')) {
            $table->addColumn('validated_at', 'datetime', ['notnull' => false]);
        }
    }
}
