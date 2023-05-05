<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Schema\v1_8;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Adds menu_template and max_traverse_level fields to MenuUpdate.
 */
class AddMenuTemplateAndMaxTraverseLevelFieldsToMenuUpdate implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        $table = $schema->getTable('oro_commerce_menu_upd');
        if (!$table->hasColumn('menu_template')) {
            $table->addColumn('menu_template', 'string', ['notnull' => false, 'length' => 255]);
        }

        if (!$table->hasColumn('max_traverse_level')) {
            $table->addColumn('max_traverse_level', 'smallint', ['notnull' => false]);
        }
    }
}
