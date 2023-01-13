<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareTrait;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCommerceMenuBundleInstaller implements
    Installation,
    AttachmentExtensionAwareInterface
{
    use AttachmentExtensionAwareTrait;

    const ORO_COMMERCE_MENU_UPDATE_TABLE_NAME = 'oro_commerce_menu_upd';
    const ORO_COMMERCE_MENU_UPDATE_TITLE_TABLE_NAME = 'oro_commerce_menu_upd_title';
    const ORO_COMMERCE_MENU_UPDATE_DESCRIPTION_TABLE_NAME = 'oro_commerce_menu_upd_descr';
    const ORO_COMMERCE_MENU_UPDATE_IMAGE_FIELD_NAME = 'image';

    const MAX_MENU_UPDATE_IMAGE_SIZE_IN_MB = 10;
    const THUMBNAIL_WIDTH_SIZE_IN_PX = 100;
    const THUMBNAIL_HEIGHT_SIZE_IN_PX = 100;

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_8';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOroCommerceMenuUpdateTable($schema);
        $this->createOroCommerceMenuUpdateTitleTable($schema);
        $this->createOroCommerceMenuUpdateDescriptionTable($schema);
        $this->createOroMenuUserAgentConditionTable($schema);

        /** Foreign keys generation **/
        $this->addOroCommerceMenuUpdateForeignKeys($schema);
        $this->addOroCommerceMenuUpdateTitleForeignKeys($schema);
        $this->addOroCommerceMenuUpdateDescriptionForeignKeys($schema);
        $this->addOroMenuUserAgentConditionForeignKeys($schema);

        /** Associations */
        $this->addOroCommerceMenuUpdateImageAssociation($schema);
    }

    /**
     * Create oro_commerce_menu_upd table.
     */
    protected function createOroCommerceMenuUpdateTable(Schema $schema)
    {
        $table = $schema->createTable(self::ORO_COMMERCE_MENU_UPDATE_TABLE_NAME);
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('key', 'string', ['length' => 100]);
        $table->addColumn('parent_key', 'string', ['length' => 100, 'notnull' => false]);
        $table->addColumn('uri', 'string', ['length' => 8190, 'notnull' => false]);
        $table->addColumn('menu', 'string', ['length' => 100]);
        $table->addColumn('icon', 'string', ['length' => 150, 'notnull' => false]);
        $table->addColumn('is_active', 'boolean', []);
        $table->addColumn('is_divider', 'boolean', []);
        $table->addColumn('is_custom', 'boolean', []);
        $table->addColumn('is_synthetic', 'boolean', ['notnull' => true, 'default' => false]);
        $table->addColumn('priority', 'integer', ['notnull' => false]);
        $table->addColumn('scope_id', 'integer', ['notnull' => true]);
        $table->addColumn('condition', 'string', ['length' => 512, 'notnull' => false]);
        $table->addColumn('screens', 'array', ['notnull' => false, 'comment' => '(DC2Type:array)']);
        $table->addColumn('system_page_route', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('content_node_id', 'integer', ['notnull' => false]);
        $table->addColumn('link_target', 'smallint', [
            'notnull' => true,
            'default' => MenuUpdate::LINK_TARGET_SAME_WINDOW,
        ]);
        $table->addColumn('menu_template', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('max_traverse_level', 'smallint', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['key', 'scope_id', 'menu'], 'oro_commerce_menu_upd_uidx');
    }

    /**
     * Create oro_commerce_menu_upd_title table
     */
    protected function createOroCommerceMenuUpdateTitleTable(Schema $schema)
    {
        $table = $schema->createTable(self::ORO_COMMERCE_MENU_UPDATE_TITLE_TABLE_NAME);
        $table->addColumn('menu_update_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['menu_update_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id']);
    }

    /**
     * Add oro_commerce_menu_upd_title foreign keys.
     */
    protected function addOroCommerceMenuUpdateTitleForeignKeys(Schema $schema)
    {
        $table = $schema->getTable(self::ORO_COMMERCE_MENU_UPDATE_TITLE_TABLE_NAME);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable(self::ORO_COMMERCE_MENU_UPDATE_TABLE_NAME),
            ['menu_update_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Create `oro_navigation_menu_upd_descr` table
     */
    protected function createOroCommerceMenuUpdateDescriptionTable(Schema $schema)
    {
        $table = $schema->createTable(self::ORO_COMMERCE_MENU_UPDATE_DESCRIPTION_TABLE_NAME);
        $table->addColumn('menu_update_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['menu_update_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id']);
    }

    /**
     * Add `oro_navigation_menu_upd_descr` foreign keys.
     */
    protected function addOroCommerceMenuUpdateDescriptionForeignKeys(Schema $schema)
    {
        $table = $schema->getTable(self::ORO_COMMERCE_MENU_UPDATE_DESCRIPTION_TABLE_NAME);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable(self::ORO_COMMERCE_MENU_UPDATE_TABLE_NAME),
            ['menu_update_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    public function addOroCommerceMenuUpdateImageAssociation(Schema $schema)
    {
        $this->attachmentExtension->addImageRelation(
            $schema,
            self::ORO_COMMERCE_MENU_UPDATE_TABLE_NAME,
            self::ORO_COMMERCE_MENU_UPDATE_IMAGE_FIELD_NAME,
            [
                'attachment' => [
                    'acl_protected' => false,
                    'use_dam' => true,
                ]
            ],
            self::MAX_MENU_UPDATE_IMAGE_SIZE_IN_MB,
            self::THUMBNAIL_WIDTH_SIZE_IN_PX,
            self::THUMBNAIL_HEIGHT_SIZE_IN_PX
        );
    }

    /**
     * Add `oro_commerce_menu_upd` foreign keys.
     */
    protected function addOroCommerceMenuUpdateForeignKeys(Schema $schema)
    {
        $table = $schema->getTable(self::ORO_COMMERCE_MENU_UPDATE_TABLE_NAME);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_scope'),
            ['scope_id'],
            ['id']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_web_catalog_content_node'),
            ['content_node_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'notnull' => false]
        );
    }

    /**
     * Create `oro_menu_user_agent_condition` table
     */
    protected function createOroMenuUserAgentConditionTable(Schema $schema)
    {
        $table = $schema->createTable('oro_menu_user_agent_condition');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('condition_group_identifier', 'integer', []);
        $table->addColumn('operation', 'string', ['length' => 32]);
        $table->addColumn('value', 'string', ['length' => 255]);
        $table->addColumn('menu_update_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Add `oro_menu_user_agent_condition` foreign keys.
     */
    protected function addOroMenuUserAgentConditionForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_menu_user_agent_condition');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_commerce_menu_upd'),
            ['menu_update_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
