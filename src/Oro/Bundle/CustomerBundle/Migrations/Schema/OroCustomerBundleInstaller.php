<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareTrait;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareTrait;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserManager;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserRoleSelectOrCreateType;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareTrait;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\ScopeBundle\Migration\Extension\ScopeExtensionAwareInterface;
use Oro\Bundle\ScopeBundle\Migration\Extension\ScopeExtensionAwareTrait;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class OroCustomerBundleInstaller implements
    Installation,
    AttachmentExtensionAwareInterface,
    ActivityExtensionAwareInterface,
    ExtendExtensionAwareInterface,
    ScopeExtensionAwareInterface
{
    use AttachmentExtensionAwareTrait;
    use ActivityExtensionAwareTrait;
    use ExtendExtensionAwareTrait;
    use ScopeExtensionAwareTrait;

    #[\Override]
    public function getMigrationVersion(): string
    {
        return 'v1_36';
    }

    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        /** Tables generation **/
        $this->createOroCustomerUserTable($schema);
        $this->createOroCustomerUserRoleTable($schema);
        $this->createOroCustomerUserAccessCustomerUserRoleTable($schema);
        $this->createOroCustomerTable($schema);
        $this->createOroCustomerGroupTable($schema);
        $this->createOroCustomerAddressTable($schema);
        $this->createOroCustomerAdrAdrTypeTable($schema);
        $this->updateOroAuditTable($schema);
        $this->createOroCustomerUserAddressTable($schema);
        $this->createOroCusUsrAdrToAdrTypeTable($schema);
        $this->createOroNavigationHistoryTable($schema);
        $this->createOroNavigationItemTable($schema);
        $this->createOroNavigationItemPinbarTable($schema);
        $this->createOroCustomerUserSdbarStTable($schema);
        $this->createOroCustomerUserSdbarWdgTable($schema);
        $this->createOroAccNavigationPagestateTable($schema);
        $this->createOroCustomerUserSettingsTable($schema);
        $this->createOroCustomerWindowsStateTable($schema);
        $this->createOroCustomerSalesRepresentativesTable($schema);
        $this->createOroCustomerUserSalesRepresentativesTable($schema);
        $this->createCustomerVisitorTable($schema);
        $this->updateOroGridViewTable($schema);
        $this->updateOroGridViewUserTable($schema);
        $this->createOroCustomerUserLoginAttemptsTable($schema);
        $this->addAuthStatusColumnToCustomerUser($schema);

        /** Foreign keys generation **/
        $this->addOroCustomerUserForeignKeys($schema);
        $this->addOroCustomerUserAccessCustomerUserRoleForeignKeys($schema);
        $this->addOroCustomerUserRoleForeignKeys($schema);
        $this->addOroCustomerForeignKeys($schema);
        $this->addOroCustomerAddressForeignKeys($schema);
        $this->addOroCustomerAdrAdrTypeForeignKeys($schema);
        $this->addOroCustomerUserAddressForeignKeys($schema);
        $this->addOroCusUsrAdrToAdrTypeForeignKeys($schema);
        $this->addOroNavigationHistoryForeignKeys($schema);
        $this->addOroNavigationItemForeignKeys($schema);
        $this->addOroNavigationItemPinbarForeignKeys($schema);
        $this->addOroCustomerUserSdbarStForeignKeys($schema);
        $this->addOroCustomerUserSdbarWdgForeignKeys($schema);
        $this->addOroAccNavigationPagestateForeignKeys($schema);
        $this->addOroCustomerUserSettingsForeignKeys($schema);
        $this->addOroCustomerWindowsStateForeignKeys($schema);
        $this->addOroCustomerSalesRepresentativesForeignKeys($schema);
        $this->addOroCustomerUserSalesRepresentativesForeignKeys($schema);
        $this->addRelationsToScope($schema);
        $this->addOroCustomerGroupForeignKeys($schema);
        $this->addOroGridViewForeignKeys($schema);
        $this->addOroGridViewUserForeignKeys($schema);
        $this->addCustomerVisitorForeignKeys($schema);
        $this->addOwnerToOroEmailAddress($schema);
        $this->addOroCustomerUserLoginAttemptsForeignKeys($schema);
    }

    /**
     * Create oro_customer_user table
     */
    private function createOroCustomerUserTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_customer_user');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('customer_id', 'integer', ['notnull' => false]);
        $table->addColumn('username', 'string', ['length' => 255]);
        $table->addColumn('email', 'string', ['length' => 255]);
        $table->addColumn('email_lowercase', 'string', ['length' => 255]);
        $table->addColumn('name_prefix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('first_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('middle_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('last_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('name_suffix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('birthday', 'date', ['notnull' => false]);
        $table->addColumn('enabled', 'boolean');
        $table->addColumn('confirmed', 'boolean');
        $table->addColumn('is_guest', 'boolean', ['default' => false]);
        $table->addColumn('salt', 'string', ['length' => 255]);
        $table->addColumn('password', 'string', ['length' => 255]);
        $table->addColumn('confirmation_token', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('password_requested', 'datetime', ['notnull' => false]);
        $table->addColumn('password_changed', 'datetime', ['notnull' => false]);
        $table->addColumn('last_login', 'datetime', ['notnull' => false]);
        $table->addColumn('login_count', 'integer', ['default' => '0', 'unsigned' => true]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->addColumn('last_duplicate_notification_date', 'datetime', ['notnull' => false]);
        $table->addColumn('website_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['email'], 'idx_oro_customer_user_email');
        $table->addIndex(['email_lowercase'], 'idx_oro_customer_user_email_lowercase');

        $this->attachmentExtension->addAttachmentAssociation(
            $schema,
            'oro_customer_user',
            [
                'image/*',
                'application/pdf',
                'application/zip',
                'application/x-gzip',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            ]
        );

        $this->activityExtension->addActivityAssociation($schema, 'oro_note', 'oro_customer_user');
        $this->activityExtension->addActivityAssociation($schema, 'oro_email', 'oro_customer_user');
    }

    /**
     * Create oro_customer table
     */
    private function createOroCustomerTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_customer');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('parent_id', 'integer', ['notnull' => false]);
        $table->addColumn('group_id', 'integer', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', ['notnull' => true]);
        $table->addColumn('updated_at', 'datetime', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['name'], 'oro_customer_name_idx');
        $table->addIndex(['created_at'], 'idx_oro_customer_created_at');
        $table->addIndex(['updated_at'], 'idx_oro_customer_updated_at');

        $this->attachmentExtension->addAttachmentAssociation(
            $schema,
            'oro_customer',
            [
                'image/*',
                'application/pdf',
                'application/zip',
                'application/x-gzip',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            ]
        );

        $this->activityExtension->addActivityAssociation($schema, 'oro_note', 'oro_customer');
        $this->extendExtension->addEnumField(
            $schema,
            'oro_customer',
            'internal_rating',
            'acc_internal_rating',
            false,
            false,
            ['dataaudit' => ['auditable' => true]]
        );
    }

    /**
     * Create oro_customer_user_access_user_role table
     */
    private function createOroCustomerUserAccessCustomerUserRoleTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_cus_user_access_role');
        $table->addColumn('customer_user_id', 'integer');
        $table->addColumn('customer_user_role_id', 'integer');
        $table->setPrimaryKey(['customer_user_id', 'customer_user_role_id']);
    }

    /**
     * Create oro_customer_group table
     */
    private function createOroCustomerGroupTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_customer_group');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['name'], 'oro_customer_group_name_idx');

        $this->activityExtension->addActivityAssociation($schema, 'oro_note', 'oro_customer_group');
    }

    /**
     * Create oro_audit table
     */
    private function updateOroAuditTable(Schema $schema): void
    {
        $auditTable = $schema->getTable('oro_audit');
        $auditTable->addColumn('customer_user_id', 'integer', ['notnull' => false]);
        $auditTable->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['customer_user_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Create oro_customer_user_role table
     */
    private function createOroCustomerUserRoleTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_customer_user_role');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('customer_id', 'integer', ['notnull' => false]);
        $table->addColumn('role', 'string', ['length' => 255]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->addColumn('self_managed', 'boolean', ['notnull' => true, 'default' => false]);
        $table->addColumn('public', 'boolean', ['notnull' => true, 'default' => true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['role']);
        $table->addUniqueIndex(['organization_id', 'customer_id', 'label']);

        $this->activityExtension->addActivityAssociation($schema, 'oro_note', 'oro_customer_user_role');

        $this->extendExtension->addManyToOneRelation(
            $schema,
            'oro_website',
            'guest_role',
            $table,
            'label',
            [
                'extend' => [
                    'owner' => ExtendScope::OWNER_CUSTOM,
                    'nullable' => true,
                    'on_delete' => 'RESTRICT',
                ],
                'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                'form' => [
                    'is_enabled' => true,
                    'form_type' => CustomerUserRoleSelectOrCreateType::class,
                    'form_options' => ['required' => true]
                ],
                'view' => ['is_displayable' => true],
                'dataaudit' => ['auditable' => true],
            ]
        );

        $this->extendExtension->addManyToOneRelation(
            $schema,
            'oro_website',
            'default_role',
            $table,
            'label',
            [
                'extend' => [
                    'owner' => ExtendScope::OWNER_CUSTOM,
                    'nullable' => true,
                    'on_delete' => 'RESTRICT',
                ],
                'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                'form' => [
                    'is_enabled' => true,
                    'form_type' => CustomerUserRoleSelectOrCreateType::class,
                    'form_options' => ['required' => true]
                ],
                'view' => ['is_displayable' => true],
                'dataaudit' => ['auditable' => true],
            ]
        );
    }

    /**
     * Create oro_customer_address table
     */
    private function createOroCustomerAddressTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_customer_address');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('system_org_id', 'integer', ['notnull' => false]);
        $table->addColumn('frontend_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('region_code', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('country_code', 'string', ['notnull' => false, 'length' => 2]);
        $table->addColumn('is_primary', 'boolean', ['notnull' => false]);
        $table->addColumn('label', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('street', 'string', ['notnull' => false, 'length' => 500]);
        $table->addColumn('street2', 'string', ['notnull' => false, 'length' => 500]);
        $table->addColumn('city', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('postal_code', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('organization', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('region_text', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('name_prefix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('first_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('middle_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('last_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('name_suffix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('phone', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('created', 'datetime');
        $table->addColumn('updated', 'datetime');
        $table->addColumn('validated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create oro_customer_adr_adr_type table
     */
    private function createOroCustomerAdrAdrTypeTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_customer_adr_adr_type');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('type_name', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('customer_address_id', 'integer', ['notnull' => false]);
        $table->addColumn('is_default', 'boolean', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['customer_address_id', 'type_name'], 'oro_customer_adr_id_type_name_idx');
    }

    /**
     * Create oro_navigation_history table
     */
    private function createOroNavigationHistoryTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_cus_navigation_history');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('customer_user_id', 'integer');
        $table->addColumn('url', 'string', ['length' => 8190]);
        $table->addColumn('title', 'text');
        $table->addColumn('visited_at', 'datetime');
        $table->addColumn('visit_count', 'integer');
        $table->addColumn('route', 'string', ['length' => 128]);
        $table->addColumn('route_parameters', 'array', ['comment' => '(DC2Type:array)']);
        $table->addColumn('entity_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['route'], 'oro_cus_nav_history_route_idx');
        $table->addIndex(['entity_id'], 'oro_cus_nav_history_entity_id_idx');
    }

    /**
     * Create oro_navigation_item table
     */
    private function createOroNavigationItemTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_cus_navigation_item');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('customer_user_id', 'integer');
        $table->addColumn('type', 'string', ['length' => 20]);
        $table->addColumn('url', 'string', ['length' => 8190]);
        $table->addColumn('title', 'text');
        $table->addColumn('position', 'smallint');
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->setPrimaryKey(['id']);
        $table->addIndex(['customer_user_id', 'position'], 'oro_sorted_items_idx');
    }

    /**
     * Create oro_cus_nav_item_pinbar table
     */
    private function createOroNavigationItemPinbarTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_cus_nav_item_pinbar');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('item_id', 'integer');
        $table->addColumn('title', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('title_short', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('maximized', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['item_id'], 'UNIQ_F6DC70B5126F525E');
    }

    /**
     * Create oro_customer_user_sdbar_st table
     */
    private function createOroCustomerUserSdbarStTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_customer_user_sdbar_st');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('customer_user_id', 'integer');
        $table->addColumn('position', 'string', ['length' => 13]);
        $table->addColumn('state', 'string', ['length' => 17]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['customer_user_id', 'position'], 'oro_cus_sdbar_st_unq_idx');
    }

    /**
     * Create oro_customer_user_sdbar_wdg table
     */
    private function createOroCustomerUserSdbarWdgTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_customer_user_sdbar_wdg');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('customer_user_id', 'integer');
        $table->addColumn('placement', 'string', ['length' => 50]);
        $table->addColumn('position', 'smallint');
        $table->addColumn('widget_name', 'string', ['length' => 50]);
        $table->addColumn('settings', 'array', ['notnull' => false, 'comment' => '(DC2Type:array)']);
        $table->addColumn('state', 'string', ['length' => 22]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['position'], 'oro_cus_sdar_wdgs_pos_idx');
        $table->addIndex(['customer_user_id', 'placement'], 'oro_cus_sdbr_wdgs_usr_place_idx');
    }

    /**
     * Create oro_cus_pagestate table
     */
    private function createOroAccNavigationPagestateTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_cus_pagestate');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('customer_user_id', 'integer');
        $table->addColumn('page_id', 'string', ['length' => 10920]);
        $table->addColumn('page_hash', 'string', ['length' => 32]);
        $table->addColumn('data', 'text');
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['page_hash'], 'UNIQ_993DC655567C7E62');
    }

    /**
     * Create oro_customer_sales_representatives table
     */
    private function createOroCustomerSalesRepresentativesTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_customer_sales_reps');
        $table->addColumn('customer_id', 'integer');
        $table->addColumn('user_id', 'integer');
        $table->setPrimaryKey(['customer_id', 'user_id']);
    }

    /**
     * Create oro_customer_user_sales_representatives table
     */
    private function createOroCustomerUserSalesRepresentativesTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_customer_user_sales_reps');
        $table->addColumn('customer_user_id', 'integer');
        $table->addColumn('user_id', 'integer');
        $table->setPrimaryKey(['customer_user_id', 'user_id']);
    }

    private function createOroCustomerUserSettingsTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_customer_user_settings');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('customer_user_id', 'integer');
        $table->addColumn('website_id', 'integer');
        $table->addColumn('currency', 'string', ['length' => 3, 'notnull' => false]);
        $table->addColumn('localization_id', 'integer', ['notnull' => false]);
        $table->addColumn('product_filters_sidebar_expanded', 'boolean', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Add oro_customer_user foreign keys.
     */
    private function addOroCustomerUserForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_customer_user');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_website'),
            ['website_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_customer_user_access_user_role foreign keys.
     */
    private function addOroCustomerUserAccessCustomerUserRoleForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_cus_user_access_role');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user_role'),
            ['customer_user_role_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['customer_user_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_customer foreign keys.
     */
    private function addOroCustomerForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_customer');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_group'),
            ['group_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $table,
            ['parent_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_customer_user_role foreign keys.
     */
    private function addOroCustomerUserRoleForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_customer_user_role');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_customer_address foreign keys.
     */
    private function addOroCustomerAddressForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_customer_address');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer'),
            ['frontend_owner_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_region'),
            ['region_code'],
            ['combined_code'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_country'),
            ['country_code'],
            ['iso2_code'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['owner_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['system_org_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
    }

    /**
     * Add oro_customer_adr_adr_type foreign keys.
     */
    private function addOroCustomerAdrAdrTypeForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_customer_adr_adr_type');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_address_type'),
            ['type_name'],
            ['name'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_address'),
            ['customer_address_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Create oro_customer_user_address table
     */
    private function createOroCustomerUserAddressTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_customer_user_address');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('system_org_id', 'integer', ['notnull' => false]);
        $table->addColumn('region_code', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('frontend_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('country_code', 'string', ['notnull' => false, 'length' => 2]);
        $table->addColumn('is_primary', 'boolean', ['notnull' => false]);
        $table->addColumn('label', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('street', 'string', ['notnull' => false, 'length' => 500]);
        $table->addColumn('street2', 'string', ['notnull' => false, 'length' => 500]);
        $table->addColumn('city', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('postal_code', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('organization', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('region_text', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('name_prefix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('first_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('middle_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('last_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('name_suffix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('phone', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('created', 'datetime');
        $table->addColumn('updated', 'datetime');
        $table->addColumn('validated_at', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Add oro_customer_user_address foreign keys.
     */
    private function addOroCustomerUserAddressForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_customer_user_address');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_region'),
            ['region_code'],
            ['combined_code'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['frontend_owner_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_country'),
            ['country_code'],
            ['iso2_code'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['owner_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['system_org_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
    }

    /**
     * Create oro_customer_adr_to_adr_type table
     */
    private function createOroCusUsrAdrToAdrTypeTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_cus_usr_adr_to_adr_type');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('type_name', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('customer_user_address_id', 'integer', ['notnull' => false]);
        $table->addColumn('is_default', 'boolean', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['customer_user_address_id', 'type_name'], 'oro_customer_user_adr_id_type_name_idx');
    }

    /**
     * Add oro_customer_adr_to_adr_type foreign keys.
     */
    private function addOroCusUsrAdrToAdrTypeForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_cus_usr_adr_to_adr_type');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_address_type'),
            ['type_name'],
            ['name'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user_address'),
            ['customer_user_address_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_navigation_history foreign keys.
     */
    private function addOroNavigationHistoryForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_cus_navigation_history');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['customer_user_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_navigation_item foreign keys.
     */
    private function addOroNavigationItemForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_cus_navigation_item');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['customer_user_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_cus_nav_item_pinbar foreign keys.
     */
    private function addOroNavigationItemPinbarForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_cus_nav_item_pinbar');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_cus_navigation_item'),
            ['item_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_customer_user_sdbar_st foreign keys.
     */
    private function addOroCustomerUserSdbarStForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_customer_user_sdbar_st');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['customer_user_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add oro_customer_user_sdbar_wdg foreign keys.
     */
    private function addOroCustomerUserSdbarWdgForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_customer_user_sdbar_wdg');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['customer_user_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add oro_cus_navigation_pagestate foreign keys.
     */
    private function addOroAccNavigationPagestateForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_cus_pagestate');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['customer_user_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Create oro_cus_windows_state table
     */
    private function createOroCustomerWindowsStateTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_cus_windows_state');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('customer_user_id', 'integer');
        $table->addColumn('data', Types::JSON_ARRAY, ['comment' => '(DC2Type:json_array)']);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['customer_user_id'], 'oro_cus_windows_state_acu_idx');
    }

    /**
     * Add oro_cus_windows_state foreign keys.
     */
    private function addOroCustomerWindowsStateForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_cus_windows_state');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['customer_user_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add oro_customer_sales_representatives foreign keys.
     */
    private function addOroCustomerSalesRepresentativesForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_customer_sales_reps');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_customer_user_sales_representatives foreign keys.
     */
    private function addOroCustomerUserSalesRepresentativesForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_customer_user_sales_reps');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['customer_user_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    private function addOroCustomerUserSettingsForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_customer_user_settings');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['customer_user_id'],
            ['id'],
            ['onDelete' => 'CASCADE'],
            'fk_customer_user_id'
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_website'),
            ['website_id'],
            ['id'],
            ['onDelete' => 'CASCADE'],
            'fk_website_id'
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_localization'),
            ['localization_id'],
            ['id'],
            ['onDelete' => 'SET NULL'],
            'fk_localization_id'
        );

        $table->addUniqueIndex(['customer_user_id', 'website_id'], 'unique_cus_user_website');
    }

    private function addRelationsToScope(Schema $schema): void
    {
        $this->scopeExtension->addScopeAssociation($schema, 'customerGroup', 'oro_customer_group', 'name');
        $this->scopeExtension->addScopeAssociation($schema, 'customer', 'oro_customer', 'name');
    }

    /**
     * Add oro_customer_group foreign keys.
     */
    private function addOroCustomerGroupForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_customer_group');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Update oro_grid_view table
     */
    private function updateOroGridViewTable(Schema $schema): void
    {
        $table = $schema->getTable('oro_grid_view');
        $table->addColumn('customer_user_owner_id', 'integer', ['notnull' => false]);
        $table->addIndex(['customer_user_owner_id'], 'idx_oro_grid_view_cust_user');
    }

    /**
     * Update oro_grid_view_user_rel table
     */
    private function updateOroGridViewUserTable(Schema $schema): void
    {
        $table = $schema->getTable('oro_grid_view_user_rel');
        $table->addColumn('customer_user_id', 'integer', ['notnull' => false]);
        $table->addIndex(['customer_user_id'], 'idx_oro_grid_view_user_cust_user');
    }

    /**
     * Create oro_customer_user_login table
     */
    private function createOroCustomerUserLoginAttemptsTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_customer_user_login');
        $table->addColumn('id', 'guid', ['notnull' => false]);
        $table->addColumn('attempt_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('success', 'boolean', ['notnull' => true]);
        $table->addColumn('source', 'integer', ['notnull' => true]);
        $table->addColumn('username', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('user_id', 'integer', ['notnull' => false]);
        $table->addColumn('ip', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('user_agent', 'text', ['notnull' => false, 'default' => '']);
        $table->addColumn('context', 'json', ['notnull' => true, 'comment' => '(DC2Type:json)']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['user_id'], 'idx_5a4c6465a76ed395');
        $table->addIndex(['attempt_at'], 'oro_cuser_log_att_at_idx');
    }

    /**
     * Add oro_grid_view foreign keys
     */
    private function addOroGridViewForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_grid_view');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['customer_user_owner_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_grid_view_user_rel foreign keys
     */
    private function addOroGridViewUserForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_grid_view_user_rel');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['customer_user_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Create oro_customer_visitor table
     */
    private function createCustomerVisitorTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_customer_visitor');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('last_visit', 'datetime');
        $table->addColumn('session_id', 'string', ['length' => 255]);
        $table->addColumn('customer_user_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['session_id'], 'idx_oro_customer_visitor_session_id');
    }

    /**
     * Add oro_customer_visitor foreign keys.
     */
    private function addCustomerVisitorForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_customer_visitor');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['customer_user_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'unique' => true, 'nullable' => true]
        );
        $table->dropIndex('IDX_F7961166BBB3772B');
        $table->addUniqueIndex(['customer_user_id'], 'UNIQ_F7961166BBB3772B');
    }

    private function addOwnerToOroEmailAddress(Schema $schema): void
    {
        $table = $schema->getTable('oro_email_address');
        $table->addColumn('owner_customeruser_id', 'integer', ['notnull' => false]);
        $table->addIndex(['owner_customeruser_id'], 'IDX_FC9DBBC5720EE070');

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['owner_customeruser_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
    }

    /**
     * Add oro_customer_user_login foreign keys.
     */
    private function addOroCustomerUserLoginAttemptsForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_customer_user_login');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['user_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    private function addAuthStatusColumnToCustomerUser(Schema $schema): void
    {
        $this->extendExtension->addEnumField(
            $schema,
            'oro_customer_user',
            'auth_status',
            'cu_auth_status',
            false,
            false,
            [
                'attribute' => ['searchable' => false, 'filterable' => true],
                'importexport' => ['excluded' => true]
            ]
        );
        $enumOptionIds = [
            ExtendHelper::buildEnumOptionId('cu_auth_status', CustomerUserManager::STATUS_ACTIVE),
            ExtendHelper::buildEnumOptionId('cu_auth_status', CustomerUserManager::STATUS_RESET),
        ];
        $schema->getTable('oro_customer_user')
            ->addExtendColumnOption(
                'auth_status',
                'enum',
                'immutable_codes',
                $enumOptionIds
            );
    }
}
