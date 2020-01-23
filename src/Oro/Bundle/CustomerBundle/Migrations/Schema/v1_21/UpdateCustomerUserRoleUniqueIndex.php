<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_21;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Update Customer User Role unique index with organization_id, customer_id and label fields.
 */
class UpdateCustomerUserRoleUniqueIndex implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_customer_user_role');
        if ($table->hasIndex('oro_customer_user_role_customer_id_label_idx')) {
            $table->dropIndex('oro_customer_user_role_customer_id_label_idx');
            $table->addUniqueIndex(['organization_id', 'customer_id', 'label']);
        }
    }
}
