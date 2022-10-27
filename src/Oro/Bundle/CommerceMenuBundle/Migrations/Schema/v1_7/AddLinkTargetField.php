<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Schema\v1_7;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Adds link_target field to MenuUpdate.
 */
class AddLinkTargetField implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        $table = $schema->getTable('oro_commerce_menu_upd');
        if (!$table->hasColumn('link_target')) {
            $table->addColumn('link_target', 'smallint', [
                'notnull' => true,
                'default' => MenuUpdate::LINK_TARGET_SAME_WINDOW,
            ]);
        }

        if ($table->hasColumn('linktargetmaint')) {
            $queries->addPostQuery(
                'UPDATE oro_commerce_menu_upd SET link_target = linktargetmaint WHERE linktargetmaint IS NOT NULL'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 0;
    }
}
