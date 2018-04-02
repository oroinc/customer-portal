<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\AttachmentBundle\Migration\GlobalAppendAllowedMimeTypesForImageQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddMimeTypeOptionForMenu implements Migration
{
    const MIME_TYPES = [
        'image/svg+xml',
    ];

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(
            new GlobalAppendAllowedMimeTypesForImageQuery(
                self::MIME_TYPES
            )
        );
    }
}
