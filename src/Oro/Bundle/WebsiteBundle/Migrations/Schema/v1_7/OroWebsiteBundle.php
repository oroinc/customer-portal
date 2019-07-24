<?php

namespace Oro\Bundle\WebsiteBundle\Migrations\Schema\v1_7;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroWebsiteBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_website');
        $indexName = 'uniq_oro_website_name';

        if ($table->hasIndex($indexName)) {
            $table->dropIndex($indexName);
        }

        $table->addUniqueIndex(['name', 'organization_id'], 'uidx_oro_website_name_organization');
    }
}
