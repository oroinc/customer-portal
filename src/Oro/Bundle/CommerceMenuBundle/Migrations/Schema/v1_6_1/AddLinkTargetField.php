<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Schema\v1_6_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Adds linkTarget field to MenuUpdate.
 */
class AddLinkTargetField implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        $table = $schema->getTable('oro_commerce_menu_upd');

        if (!$table->hasColumn('linkTargetMaint')) {
            $table->addColumn('linkTargetMaint', 'smallint', [
                'notnull' => true,
                'default' => MenuUpdate::LINK_TARGET_SAME_WINDOW,
                OroOptions::KEY => [
                    'entity' => ['label' => 'oro.commercemenu.menuupdate.link_target.label'],
                    'extend' => [
                        'owner' => ExtendScope::OWNER_SYSTEM,
                        'is_extend' => true,
                    ]
                ]
            ]);
        }
    }
}
