<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_14;

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
        $this->updateCustomerUserTable($schema);

        /** Tables generation **/
        $this->createCustomerVisitorTable($schema);
    }

    /**
     * Update oro_customer_user table
     *
     * @param Schema $schema
     */
    private function updateCustomerUserTable(Schema $schema)
    {
        $table = $schema->getTable('oro_customer_user');
        $table->addColumn('is_guest', 'boolean', []);

        //remove uniq indices for name and email fields
        $table->dropIndex('UNIQ_9511CEB5F85E0677');
        $table->dropIndex('uniq_oro_customer_user_email');
    }

    /**
     * Create oro_customer_visitor table
     *
     * @param Schema $schema
     */
    protected function createCustomerVisitorTable(Schema $schema)
    {
        $table = $schema->createTable('oro_customer_visitor');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('last_visit', 'datetime', []);
        $table->addColumn('session_id', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['id', 'session_id'], 'id_session_id_idx');
    }
}
