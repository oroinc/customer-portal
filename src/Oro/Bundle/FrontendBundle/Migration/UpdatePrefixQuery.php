<?php

namespace Oro\Bundle\FrontendBundle\Migration;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

/**
 * Migration to update prefix from orob2b_ to oro_
 */
class UpdatePrefixQuery extends ParametrizedMigrationQuery
{
    /**
     * @var string
     */
    private $table;

    /**
     * @var string
     */
    private $field;

    /**
     * @param string $table
     * @param string $field
     */
    public function __construct($table, $field)
    {
        $this->table = $table;
        $this->field = $field;
    }

    #[\Override]
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $this->processQueries($logger, true);

        return $logger->getMessages();
    }

    #[\Override]
    public function execute(LoggerInterface $logger)
    {
        $this->processQueries($logger);
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     */
    protected function processQueries(LoggerInterface $logger, $dryRun = false)
    {
        $table = $this->table;
        $field = $this->field;

        $statement = $this->connection->executeQuery("SELECT id, $field FROM $table");

        while ($entity = $statement->fetchAssociative()) {
            $originalRoute = $entity[$field];
            $alteredRoute = str_replace('orob2b_', 'oro_', $originalRoute);

            if ($alteredRoute !== $originalRoute) {
                $query = "UPDATE $table SET $field = ? WHERE id = ?";
                $parameters = [$alteredRoute, $entity['id']];

                $this->logQuery($logger, $query, $parameters);
                if (!$dryRun) {
                    $this->connection->executeStatement($query, $parameters);
                }
            }
        }
    }
}
