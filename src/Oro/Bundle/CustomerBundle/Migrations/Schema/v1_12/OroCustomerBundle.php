<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_12;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCustomerBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $schema->dropTable('oro_customer_user_org');

        $queries->addQuery(new UpdateCustomerUserACLQuery());
        $queries->addQuery(new UpdateAnonymousCustomerGroupOwnership());

        //remove invalid record because of error there is a NULL value
        $this->removeFromConfig($queries, 'default_customer_owner');

        $this->addOwnership($schema);
    }

    /**
     * @param QueryBag $queries
     * @param string   $name
     */
    protected function removeFromConfig(QueryBag $queries, $name)
    {
        $queries->addQuery(new ParametrizedSqlMigrationQuery(
            'DELETE FROM oro_config_value WHERE name = :name AND section = :section AND text_value IS NULL',
            ['name' => $name, 'section' => 'oro_customer']
        ));
    }

    protected function addOwnership(Schema $schema)
    {
        $table = $schema->getTable('oro_customer_group');
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
