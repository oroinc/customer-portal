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
        $this->updateCustomerTable($schema);
    }

    /**
     * Update oro_customer table
     *
     * @param Schema $schema
     */
    private function updateCustomerTable(Schema $schema)
    {
        $table = $schema->getTable('oro_customer');
        if (!$table->hasColumn('created_at')) {
            $table->addColumn('created_at', 'datetime', []);
        }
        if (!$table->hasIndex('idx_oro_customer_created_at')) {
            $table->addIndex(['created_at'], 'idx_oro_customer_created_at', []);
        }
        if (!$table->hasColumn('updated_at')) {
            $table->addColumn('updated_at', 'datetime', []);
        }
        if (!$table->hasIndex('idx_oro_customer_updated_at')) {
            $table->addIndex(['updated_at'], 'idx_oro_customer_updated_at', []);
        }
    }
}
