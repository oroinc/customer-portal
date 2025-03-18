<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_32_1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateCustomerVisitorSessionIdIndex implements Migration
{
    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $table = $schema->getTable('oro_customer_visitor');
        if ($table->hasIndex('id_session_id_idx')) {
            $table->dropIndex('id_session_id_idx');
        }
        if (!$table->hasIndex('idx_oro_customer_visitor_session_id')) {
            $table->addIndex(['session_id'], 'idx_oro_customer_visitor_session_id');
        }
    }
}
