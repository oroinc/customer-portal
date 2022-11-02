<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_24;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\SqlMigrationQuery;

class UpdateParentCustomerCircularReferenceToItself implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(
            new SqlMigrationQuery('UPDATE oro_customer SET parent_id = NULL WHERE parent_id = id')
        );
    }
}
