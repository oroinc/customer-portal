<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Schema\v1_8;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Adds menu_template and depth fields to MenuUpdate.
 */
class AddMenuTemplateAndDepthFieldsToMenuUpdate implements Migration
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
        if (!$table->hasColumn('depth')) {
            $table->addColumn('depth', 'smallint', ['notnull' => false]);
        }
    }
}
