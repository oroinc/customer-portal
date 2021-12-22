<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_27;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Adds `product_filters_sidebar_expanded` field to the `oro_customer_user_settings` table.
 */
class AddProductFiltersSidebarStateToCustomerUserSettings implements Migration
{
    private const PRODUCT_FILTERS_SIDEBAR_EXPANDED_FIELD_NAME = 'product_filters_sidebar_expanded';

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->addProductFiltersSidebarStateField($schema);
    }

    private function addProductFiltersSidebarStateField(Schema $schema): void
    {
        $table = $schema->getTable('oro_customer_user_settings');

        if (!$table->hasColumn(self::PRODUCT_FILTERS_SIDEBAR_EXPANDED_FIELD_NAME)) {
            $table->addColumn(self::PRODUCT_FILTERS_SIDEBAR_EXPANDED_FIELD_NAME, 'boolean', ['notnull' => false]);
        }
    }
}
