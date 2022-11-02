<?php

namespace Oro\Bundle\FrontendImportExportBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Handles all migrations executed during installation.
 */
class OroFrontendImportExportBundleInstaller implements Installation
{
    /**
     * @inheritDoc
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * @inheritDoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createImportExportTable($schema);
    }

    private function createImportExportTable(Schema $schema)
    {
        $table = $schema->createTable('oro_frontend_import_export_result');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('customer_id', 'integer', ['notnull' => false]);
        $table->addColumn('customer_user_id', 'integer', ['notnull' => false]);
        $table->addColumn('filename', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('job_id', 'integer', ['unique' => true, 'notnull' => true]);
        $table->addColumn('type', 'string', ['unique' => false, 'length' => 255, 'notnull' => true]);
        $table->addColumn('entity', 'string', ['unique' => false, 'length' => 255, 'notnull' => true]);
        $table->addColumn('options', 'array', ['notnull' => false, 'comment' => '(DC2Type:array)']);
        $table->addColumn('expired', 'boolean', ['default' => '0']);
        $table->addColumn('created_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['filename']);
        $table->addUniqueIndex(['job_id']);
        $table->addIndex(['owner_id']);
        $table->addIndex(['organization_id']);
        $table->addIndex(['customer_user_id']);

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['customer_user_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }
}
