<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_29;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Makes unique index by 'customer_user_id' unique
 */
class CustomerVisitorMakeCustomerUserIndexUnique implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_customer_visitor');

        $table->dropIndex('idx_customer_visitor_id_customer_user_id');

        if ($table->hasIndex('IDX_F7961166BBB3772B')) {
            $table->dropIndex('IDX_F7961166BBB3772B');
        }

        $table->addUniqueIndex(['customer_user_id'], 'UNIQ_F7961166BBB3772B');
    }
}
