<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Schema\v1_6;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Adds contentNode, systemPageRoute relations to MenuUpdate.
 */
class AddPageTargetRelations implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        $table = $schema->getTable('oro_commerce_menu_upd');

        if (!$table->hasColumn('system_page_route')) {
            $table->addColumn('system_page_route', 'string', ['length' => 255, 'notnull' => false]);
        }

        if (!$table->hasColumn('content_node_id')) {
            $table->addColumn('content_node_id', 'integer', ['notnull' => false]);
            $table->addForeignKeyConstraint(
                $schema->getTable('oro_web_catalog_content_node'),
                ['content_node_id'],
                ['id'],
                ['onDelete' => 'SET NULL', 'notnull' => false]
            );
        }
    }
}
