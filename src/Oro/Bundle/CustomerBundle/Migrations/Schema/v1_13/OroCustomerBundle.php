<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_13;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCustomerBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables modifications **/
        $this->updateOroGridViewTable($schema);
        $this->updateOroGridViewUserTable($schema);

        /** Foreign keys generation **/
        $this->addOroGridViewForeignKeys($schema);
        $this->addOroGridViewUserForeignKeys($schema);
    }

    /**
     * Update oro_grid_view table
     */
    private function updateOroGridViewTable(Schema $schema)
    {
        $table = $schema->getTable('oro_grid_view');
        $table->addColumn('customer_user_owner_id', 'integer', ['notnull' => false]);
        $table->addIndex(['customer_user_owner_id'], 'idx_oro_grid_view_cust_user');
    }

    /**
     * Update oro_grid_view_user_rel table
     */
    private function updateOroGridViewUserTable(Schema $schema)
    {
        $table = $schema->getTable('oro_grid_view_user_rel');
        $table->addColumn('customer_user_id', 'integer', ['notnull' => false]);
        $table->addIndex(['customer_user_id'], 'idx_oro_grid_view_user_cust_user');
    }

    /**
     * Add oro_grid_view foreign keys
     */
    private function addOroGridViewForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_grid_view');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['customer_user_owner_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_grid_view_user_rel foreign keys
     */
    private function addOroGridViewUserForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_grid_view_user_rel');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['customer_user_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
