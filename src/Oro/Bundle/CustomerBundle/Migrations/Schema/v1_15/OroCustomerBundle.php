<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_15;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCustomerBundle implements Migration
{
    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $this->updateCustomerVisitorTable($schema);
        $this->updateCustomerUserTable($schema);
    }

    private function updateCustomerVisitorTable(Schema $schema): void
    {
        $table = $schema->getTable('oro_customer_visitor');
        $table->addColumn('customer_user_id', 'integer', ['notnull' => false]);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['customer_user_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addUniqueIndex(['customer_user_id'], 'idx_customer_visitor_id_customer_user_id');
    }

    private function updateCustomerUserTable(Schema $schema): void
    {
        $table = $schema->getTable('oro_customer_user');
        $table->addColumn('is_guest', 'boolean', ['default' => false]);

        //remove uniq indices for name and email fields
        $table->dropIndex('UNIQ_9511CEB5F85E0677');
        $table->dropIndex('uniq_oro_customer_user_email');
    }
}
