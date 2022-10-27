<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_23;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddOwnerToOroEmailAddress implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->addOwnerToOroEmailAddress($schema);
    }

    /**
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function addOwnerToOroEmailAddress(Schema $schema)
    {
        $table = $schema->getTable('oro_email_address');

        if ($table->hasColumn('owner_customeruser_id')) {
            return;
        }

        $table->addColumn('owner_customeruser_id', 'integer', ['notnull' => false]);
        $table->addIndex(['owner_customeruser_id'], 'IDX_FC9DBBC5720EE070', []);

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['owner_customeruser_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }
}
