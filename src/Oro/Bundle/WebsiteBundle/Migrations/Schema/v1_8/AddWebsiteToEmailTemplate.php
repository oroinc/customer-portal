<?php

namespace Oro\Bundle\WebsiteBundle\Migrations\Schema\v1_8;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigModel;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManager;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddWebsiteToEmailTemplate implements Migration, ExtendExtensionAwareInterface
{
    use ExtendExtensionAwareTrait;

    public function up(Schema $schema, QueryBag $queries): void
    {
        if (!$schema->hasTable('oro_website') ||
            !$schema->hasTable('oro_email_template') ||
            $schema->getTable('oro_email_template')->hasColumn('website_id')) {
            return;
        }

        $this->extendExtension->addManyToOneRelation(
            $schema,
            'oro_email_template',
            'website',
            'oro_website',
            'name',
            [
                ExtendOptionsManager::MODE_OPTION => ConfigModel::MODE_READONLY,
                'extend' => [
                    'is_extend' => true,
                    'owner' => ExtendScope::OWNER_CUSTOM,
                    'nullable' => true,
                    'on_delete' => 'CASCADE',
                ],
                'datagrid' => [
                    'is_visible' => DatagridScope::IS_VISIBLE_TRUE,
                    'show_filter' => true,
                ],
                'form' => ['is_enabled' => false],
                'merge' => ['display' => false],
                'dataaudit' => ['auditable' => true],
            ]
        );

        $emailTemplateTable = $schema->getTable('oro_email_template');
        if ($emailTemplateTable->hasIndex('UQ_NAME')) {
            $emailTemplateTable->dropIndex('UQ_NAME');
        }

        $emailTemplateTable->addUniqueIndex(['name', 'entityName', 'website_id'], 'UQ_NAME');
    }
}
