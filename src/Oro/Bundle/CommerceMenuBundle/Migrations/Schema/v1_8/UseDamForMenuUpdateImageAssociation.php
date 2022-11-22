<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Schema\v1_8;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Update "use_dam" entity field config option for "image" field of MenuUpdate entity.
 */
class UseDamForMenuUpdateImageAssociation implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        $queries->addPostQuery(
            new UpdateEntityConfigFieldValueQuery(MenuUpdate::class, 'image', 'attachment', 'use_dam', true)
        );
    }
}
