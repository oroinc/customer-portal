<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_17;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\EntityConfigBundle\Migration\RemoveOneToManyRelationQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Removes CustomerGroup::customers relation data from oro_entity_field_config table
 */
class RemoveCustomerGroupCustomersRelation implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(
            new RemoveOneToManyRelationQuery(
                CustomerGroup::class,
                'customers'
            )
        );
    }
}
