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
    }

    /**
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
}
