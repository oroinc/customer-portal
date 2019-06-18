<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\CommerceMenuBundle\Entity\MenuUpdate;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Update acl_protected entity field config option for image field of MenuUpdate entity.
 */
class UpdateAttachmentFieldConfigForMenuUpdateImage implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addPostQuery(
            new UpdateEntityConfigFieldValueQuery(MenuUpdate::class, 'image', 'attachment', 'acl_protected', false)
        );
    }
}
