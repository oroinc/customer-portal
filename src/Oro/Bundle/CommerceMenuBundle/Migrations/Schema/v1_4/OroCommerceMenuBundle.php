<?php

namespace Oro\Bundle\CommerceMenuBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Psr\Log\LoggerInterface;

class OroCommerceMenuBundle extends ParametrizedMigrationQuery implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $this->setNullForEmptyScreens($logger, true);

        return $logger->getMessages();
    }

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->updateOroCommerceMenuUpdateTable($schema);
    }

    /**
     * {@inheritDoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $this->setNullForEmptyScreens($logger);
    }

    /**
     * Make screens column nullable.
     */
    private function updateOroCommerceMenuUpdateTable(Schema $schema)
    {
        $table = $schema->getTable('oro_commerce_menu_upd');
        $table->changeColumn('screens', ['notnull' => false]);
    }

    /**
     * Fixes screens column values for the case when the column has been already created as not nullable while table
     * was not empty.
     *
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     */
    private function setNullForEmptyScreens(LoggerInterface $logger, $dryRun = false)
    {
        // Issue is actual only on MySQL.
        if (!$this->connection->getDatabasePlatform() instanceof MySqlPlatform) {
            return;
        }

        $query = "UPDATE oro_commerce_menu_upd SET screens = NULL WHERE screens = ''";

        $this->logQuery($logger, $query);
        if (!$dryRun) {
            $this->connection->executeStatement($query);
        }
    }
}
