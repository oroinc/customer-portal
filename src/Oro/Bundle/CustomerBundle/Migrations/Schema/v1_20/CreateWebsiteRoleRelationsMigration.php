<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_20;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\CustomerBundle\Form\Type\CustomerUserRoleSelectOrCreateType;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class CreateWebsiteRoleRelationsMigration implements Migration, OrderedMigrationInterface, ExtendExtensionAwareInterface
{
    /** @var ExtendExtension */
    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 10;
    }

    /**
     * {@inheritdoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_website');
        if (!$table->hasColumn('guest_role_id')) {
            $this->extendExtension->addManyToOneRelation(
                $schema,
                $table,
                'guest_role',
                'oro_customer_user_role',
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

        if (!$table->hasColumn('default_role_id')) {
            $this->extendExtension->addManyToOneRelation(
                $schema,
                $table,
                'default_role',
                'oro_customer_user_role',
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
    }
}
