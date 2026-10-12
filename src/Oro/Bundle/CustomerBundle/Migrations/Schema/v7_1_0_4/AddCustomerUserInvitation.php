<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v7_1_0_4;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\CustomerBundle\Entity\CustomerUserInvitation;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareTrait;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddCustomerUserInvitation implements Migration, ExtendExtensionAwareInterface
{
    use ExtendExtensionAwareTrait;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        if ($schema->hasTable('oro_customer_user_invitation')) {
            return;
        }

        $table = $schema->createTable('oro_customer_user_invitation');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer');
        $table->addColumn('website_id', 'integer');
        $table->addColumn('customer_id', 'integer');
        $table->addColumn('invited_by_id', 'integer', ['notnull' => false]);
        $table->addColumn('accepted_user_id', 'integer', ['notnull' => false]);
        $table->addColumn('email', 'string', ['length' => 255]);
        $table->addColumn('email_lowercase', 'string', ['length' => 255]);
        $table->addColumn('token_hash', 'string', ['length' => 64, 'notnull' => false]);
        $table->addColumn('expires_at', 'datetime');
        $table->addColumn('sent_at', 'datetime', ['notnull' => false]);
        $table->addColumn('accepted_at', 'datetime', ['notnull' => false]);
        $table->addColumn('revoked_at', 'datetime', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->setPrimaryKey(['id']);
        $table->addIndex(['organization_id', 'email'], 'oro_cus_usr_inv_org_email_idx');
        $table->addIndex(['organization_id', 'email_lowercase'], 'oro_cus_usr_inv_org_eml_lc_idx');
        $table->addUniqueIndex(['token_hash'], 'oro_cus_usr_inv_token_uidx');
        $table->addIndex(['customer_id'], 'oro_cus_usr_inv_customer_idx');

        $this->extendExtension->addEnumField(
            $schema,
            'oro_customer_user_invitation',
            'status',
            CustomerUserInvitation::INTERNAL_STATUS_CODE,
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
                'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_TRUE],
                'dataaudit' => ['auditable' => true],
                'email' => ['available_in_template' => true],
            ]
        );
        $table->addExtendColumnOption(
            'status',
            'enum',
            'immutable_codes',
            [
                ExtendHelper::buildEnumOptionId(
                    CustomerUserInvitation::INTERNAL_STATUS_CODE,
                    CustomerUserInvitation::STATUS_PENDING
                ),
                ExtendHelper::buildEnumOptionId(
                    CustomerUserInvitation::INTERNAL_STATUS_CODE,
                    CustomerUserInvitation::STATUS_ACCEPTED
                ),
                ExtendHelper::buildEnumOptionId(
                    CustomerUserInvitation::INTERNAL_STATUS_CODE,
                    CustomerUserInvitation::STATUS_REVOKED
                ),
            ]
        );

        $rolesTable = $schema->createTable('oro_cus_usr_inv_role');
        $rolesTable->addColumn('invitation_id', 'integer');
        $rolesTable->addColumn('role_id', 'integer');
        $rolesTable->setPrimaryKey(['invitation_id', 'role_id']);

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_website'),
            ['website_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['invited_by_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user'),
            ['accepted_user_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $rolesTable->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user_invitation'),
            ['invitation_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $rolesTable->addForeignKeyConstraint(
            $schema->getTable('oro_customer_user_role'),
            ['role_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
    }
}
