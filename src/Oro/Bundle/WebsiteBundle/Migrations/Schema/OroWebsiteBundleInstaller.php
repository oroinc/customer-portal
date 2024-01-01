<?php

namespace Oro\Bundle\WebsiteBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareTrait;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\ScopeBundle\Migration\Extension\ScopeExtensionAwareInterface;
use Oro\Bundle\ScopeBundle\Migration\Extension\ScopeExtensionAwareTrait;

class OroWebsiteBundleInstaller implements
    Installation,
    ActivityExtensionAwareInterface,
    ExtendExtensionAwareInterface,
    ScopeExtensionAwareInterface
{
    use ActivityExtensionAwareTrait;
    use ExtendExtensionAwareTrait;
    use ScopeExtensionAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function getMigrationVersion(): string
    {
        return 'v1_7';
    }

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        /** Tables generation **/
        $this->createOroRelatedWebsiteTable($schema);
        $this->createOroWebsiteTable($schema);

        /** Foreign keys generation **/
        $this->addOroRelatedWebsiteForeignKeys($schema);
        $this->addOroWebsiteForeignKeys($schema);

        $this->activityExtension->addActivityAssociation($schema, 'oro_note', 'oro_website');
        $this->scopeExtension->addScopeAssociation($schema, 'website', 'oro_website', 'name');
    }

    /**
     * Create oro_related_website table
     */
    private function createOroRelatedWebsiteTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_related_website');
        $table->addColumn('website_id', 'integer');
        $table->addColumn('related_website_id', 'integer');
        $table->setPrimaryKey(['website_id', 'related_website_id']);
    }

    /**
     * Create oro_website table
     */
    private function createOroWebsiteTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_website');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('business_unit_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->addColumn('is_default', 'boolean');
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['name', 'organization_id'], 'uidx_oro_website_name_organization');
        $table->addIndex(['created_at'], 'idx_oro_website_created_at');
        $table->addIndex(['updated_at'], 'idx_oro_website_updated_at');
    }

    /**
     * Add oro_related_website foreign keys.
     */
    private function addOroRelatedWebsiteForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_related_website');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_website'),
            ['website_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_website'),
            ['related_website_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_website foreign keys.
     */
    private function addOroWebsiteForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_website');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_business_unit'),
            ['business_unit_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
