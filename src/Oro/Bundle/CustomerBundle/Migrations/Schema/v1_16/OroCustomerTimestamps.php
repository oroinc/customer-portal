<?php

namespace Oro\Bundle\CustomerBundle\Migrations\Schema\v1_16;

use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Extension\DatabasePlatformAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Extension\DatabasePlatformAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCustomerTimestamps implements Migration, DatabasePlatformAwareInterface
{
    use DatabasePlatformAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $schemaBefore = clone $schema;

        $table = $schemaBefore->getTable('oro_customer');
        if ($table->hasColumn('created_at') === false) {
            $table->addColumn('created_at', 'datetime', ['notnull' => false]);
        }

        if ($table->hasColumn('updated_at') === false) {
            $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        }

        foreach ($this->getSchemaDiff($schema, $schemaBefore) as $query) {
            $queries->addQuery($query);
        }

        $now = (new \DateTime())->format('Y-m-d H:i:s');
        $queries->addQuery(
            sprintf(
                "UPDATE oro_customer SET created_at = '%s', updated_at = '%s' WHERE created_at IS NULL",
                $now,
                $now
            )
        );

        $schemaAfter = clone $schemaBefore;

        $table = $schemaAfter->getTable('oro_customer');
        $table->changeColumn('created_at', ['notnull' => true]);
        $table->changeColumn('updated_at', ['notnull' => true]);
        if ($table->hasIndex('idx_oro_customer_updated_at') === false) {
            $table->addIndex(['updated_at'], 'idx_oro_customer_updated_at', []);
        }
        if ($table->hasIndex('idx_oro_customer_created_at') === false) {
            $table->addIndex(['created_at'], 'idx_oro_customer_created_at', []);
        }

        foreach ($this->getSchemaDiff($schemaBefore, $schemaAfter) as $query) {
            $queries->addQuery($query);
        }
    }

    /**
     * @param Schema $schema
     * @param Schema $toSchema
     *
     * @return array
     */
    protected function getSchemaDiff(Schema $schema, Schema $toSchema)
    {
        $comparator = new Comparator();
        return $comparator->compare($schema, $toSchema)->toSql($this->platform);
    }
}
