<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddScopeToMenuUpdateTable implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 2;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Table updates **/
        $this->updateOroCommerceMenuUpdateTable($schema);
        $this->addOroCommerceMenuUpdateForeignKeys($schema);
    }

    /**
     * Update oro_commerce_menu_upd
     *
     * @param Schema $schema
     */
    protected function updateOroCommerceMenuUpdateTable(Schema $schema)
    {
        $table = $schema->getTable('oro_commerce_menu_upd');
        $table->addColumn('scope_id', 'integer', ['notnull' => true]);
        $table->addUniqueIndex(['key', 'scope_id', 'menu'], 'oro_commerce_menu_upd_uidx');
    }

    /**
     * Add oro_commerce_menu_upd foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroCommerceMenuUpdateForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_commerce_menu_upd');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_scope'),
            ['scope_id'],
            ['id']
        );
    }
}
