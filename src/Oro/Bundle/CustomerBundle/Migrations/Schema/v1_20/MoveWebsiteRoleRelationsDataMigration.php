<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_20;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MoveWebsiteRoleRelationsDataMigration implements Migration, OrderedMigrationInterface
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
        $queries->addQuery('
            UPDATE oro_website AS w
            SET default_role_id = (
                SELECT customer_user_role_id
                FROM oro_customer_role_to_website AS r
                WHERE r.website_id = w.id
            )
            WHERE default_role_id IS NULL;
        ');

        $queries->addQuery("
            UPDATE oro_website
            SET guest_role_id = (
                SELECT id
                FROM oro_customer_user_role AS r
                WHERE r.role = 'ROLE_FRONTEND_ANONYMOUS'
            )
            WHERE guest_role_id IS NULL;
        ");
    }
}
