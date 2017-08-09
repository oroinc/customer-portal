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
    }

    /**
     * Update oro_commerce_menu_upd
     *
     * @param Schema $schema
     */
    protected function updateOroCommerceMenuUpdateTable(Schema $schema)
    {
        $table = $schema->getTable('oro_commerce_menu_upd');
        $table->addColumn('screens', 'array', ['comment' => '(DC2Type:array)']);
    }
}
