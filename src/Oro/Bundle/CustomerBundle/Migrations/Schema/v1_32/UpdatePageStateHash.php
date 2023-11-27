<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_32;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\SqlMigrationQuery;

/**
 * Updates the hashes of pinned pages, since the hash must contain the customer user ID.
 */
class UpdatePageStateHash implements Migration
{
    public function up(Schema $schema, QueryBag $queries): void
    {
        $queries->addPreQuery(
            new SqlMigrationQuery(
                'UPDATE oro_cus_pagestate SET page_hash = MD5(CONCAT(page_id, \'_\', customer_user_id))'
            )
        );
    }
}
