<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCommerceMenuBundle implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateOroCommerceMenuUpdateTable($schema);
        $this->createOroMenuUserAgentConditionTable($schema);
        $this->addOroMenuUserAgentConditionForeignKeys($schema);
    }

    /**
     * Update oro_commerce_menu_upd
     */
    protected function updateOroCommerceMenuUpdateTable(Schema $schema)
    {
        $table = $schema->getTable('oro_commerce_menu_upd');
        $table->addColumn('screens', 'array', ['notnull' => false, 'comment' => '(DC2Type:array)']);
    }

    /**
     * Create `oro_menu_user_agent_condition` table
     */
    protected function createOroMenuUserAgentConditionTable(Schema $schema)
    {
        $table = $schema->createTable('oro_menu_user_agent_condition');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('condition_group_identifier', 'integer', []);
        $table->addColumn('operation', 'string', ['length' => 32]);
        $table->addColumn('value', 'string', ['length' => 255]);
        $table->addColumn('menu_update_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Add `oro_menu_user_agent_condition` foreign keys.
     */
    protected function addOroMenuUserAgentConditionForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_menu_user_agent_condition');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_commerce_menu_upd'),
            ['menu_update_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
