<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_16;

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
        /** Tables generation **/
        $this->updateCustomerVisitorTable($schema);
    }

    /**
     * Update oro_customer_visitor table
     *
     * @param Schema $schema
     */
    protected function updateCustomerVisitorTable(Schema $schema)
    {
        $table = $schema->getTable('oro_customer_visitor');
        $table->addColumn('customer_user_id', 'integer', ['notnull' => false]);
    }
}
