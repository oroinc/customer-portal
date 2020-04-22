<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Schema\v1_7;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\EntityConfigBundle\Migration\RemoveFieldQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Drop old extended linktargetmaint field from MenuUpdate.
 */
class DropOldLinkTargetField implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        $table = $schema->getTable('oro_commerce_menu_upd');
        if ($table->hasColumn('linktargetmaint')) {
            $table->dropColumn('linktargetmaint');

            $queries->addPostQuery(new RemoveFieldQuery(MenuUpdate::class, 'linkTargetMaint'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }
}
