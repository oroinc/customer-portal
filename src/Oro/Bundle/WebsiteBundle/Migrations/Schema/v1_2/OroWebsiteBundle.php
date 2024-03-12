<?php

namespace Oro\Bundle\WebsiteBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroWebsiteBundle implements Migration, ActivityExtensionAwareInterface
{
    use ActivityExtensionAwareTrait;

    private const WEBSITE_TABLE_NAME = 'orob2b_website';

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        $this->addIndexForCreateAndUpdateFields($schema);
        $this->addNoteAssociations($schema);
        $this->allowNullOnUrl($schema);
    }

    private function addIndexForCreateAndUpdateFields(Schema $schema): void
    {
        $table = $schema->getTable(self::WEBSITE_TABLE_NAME);
        $table->addIndex(['created_at'], 'idx_orob2b_website_created_at', []);
        $table->addIndex(['updated_at'], 'idx_orob2b_website_updated_at', []);
    }

    private function addNoteAssociations(Schema $schema): void
    {
        $this->activityExtension->addActivityAssociation($schema, 'oro_note', self::WEBSITE_TABLE_NAME);
    }

    private function allowNullOnUrl(Schema $schema): void
    {
        $table = $schema->getTable(self::WEBSITE_TABLE_NAME);
        $table->getColumn('url')->setNotnull(false);
    }
}
