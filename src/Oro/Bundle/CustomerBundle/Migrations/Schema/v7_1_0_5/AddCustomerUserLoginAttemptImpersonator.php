<?php

declare(strict_types=1);

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v7_1_0_5;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddCustomerUserLoginAttemptImpersonator implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_customer_user_login');

        $table->addColumn('impersonator_id', 'integer', ['notnull' => false]);
        $table->addIndex(['impersonator_id'], 'oro_cus_usr_login_imp_idx');

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['impersonator_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }
}
