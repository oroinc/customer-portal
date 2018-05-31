<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_17;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Creates "oro_customer_user_api" table.
 */
class AddCustomerUserApiTable implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('oro_customer_user_api')) {
            return;
        }

        $table = $schema->createTable('oro_customer_user_api');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_id', 'integer', []);
        $table->addColumn('api_key', 'crypted_string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['api_key'], 'UNIQ_824F80CCC912ED9D');
        $table->addIndex(['user_id'], 'IDX_824F80CCA76ED395', []);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['user_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
