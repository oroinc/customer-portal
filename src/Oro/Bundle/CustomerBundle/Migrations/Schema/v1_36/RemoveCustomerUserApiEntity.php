<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_36;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityConfigBundle\Migration\RemoveFieldQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RemoveCustomerUserApiEntity implements Migration
{
    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        if (!$schema->hasTable('oro_customer_user_api')) {
            return;
        }

        $schema->dropTable('oro_customer_user_api');
        $queries->addQuery(new RemoveFieldQuery(CustomerUser::class, 'apiKeys'));
    }
}
