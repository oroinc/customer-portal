<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Schema\v1_8;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Enables cascade delete on content_node_id column.
 */
class SetCascadeDeleteForContentNode implements Migration
{
    public function up(Schema $schema, QueryBag $queries): void
    {
        $table = $schema->getTable('oro_commerce_menu_upd');
        if ($table->hasColumn('content_node_id')) {
            foreach ($table->getForeignKeys() as $foreignKeyConstraint) {
                if ($foreignKeyConstraint->getForeignTableName() === 'oro_web_catalog_content_node'
                    && $foreignKeyConstraint->getLocalColumns() === ['content_node_id']
                    && $foreignKeyConstraint->getForeignColumns() === ['id']) {
                    $table->removeForeignKey($foreignKeyConstraint->getName());
                    $table->addForeignKeyConstraint(
                        $schema->getTable('oro_web_catalog_content_node'),
                        ['content_node_id'],
                        ['id'],
                        ['onDelete' => 'CASCADE', 'notnull' => false]
                    );
                    break;
                }
            }
        }
    }
}
