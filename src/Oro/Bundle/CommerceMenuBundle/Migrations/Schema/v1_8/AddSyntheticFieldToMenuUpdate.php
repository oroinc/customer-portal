<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Schema\v1_8;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Adds is_synthetic fields to MenuUpdate.
 */
class AddSyntheticFieldToMenuUpdate implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        $table = $schema->getTable('oro_commerce_menu_upd');
        if (!$table->hasColumn('is_synthetic')) {
            $table->addColumn('is_synthetic', 'boolean', ['notnull' => true, 'default' => false]);
        }
    }
}
