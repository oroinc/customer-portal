<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_18;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\CustomerBundle\Migrations\Schema\OroCustomerBundleInstaller;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Make column not nullable after it is filled for all rows.
 */
class MakeEmailLowercaseFieldNotNull implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 20;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable(OroCustomerBundleInstaller::ORO_CUSTOMER_USER_TABLE_NAME);
        $table->changeColumn('email_lowercase', ['notnull' => true]);
    }
}
