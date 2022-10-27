<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_21;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityConfigBundle\Migration\RemoveFieldQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Removes "organizations" fields from CustomerUser entity configs.
 */
class RemoveOrganizationsField implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(new RemoveFieldQuery(
            'Oro\Bundle\CustomerBundle\Entity\CustomerUser',
            'organizations'
        ));
    }
}
