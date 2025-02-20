<?php

namespace Oro\Bundle\FrontendBundle\Migration;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

/**
 * Migration to update clas name  from OroB2B to Oro
 */
class UpdateClassNamesQuery extends ParametrizedMigrationQuery
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

        $statement = $this->connection->executeQuery("SELECT id, $field FROM $table WHERE $field LIKE 'OroB2B%'");

        while ($entity = $statement->fetchAssociative()) {
            $originalClass = $entity[$field];
            $class = preg_replace('/^OroB2B/', 'Oro', $originalClass, 1);

            $query = "UPDATE $table SET $field = ? WHERE id = ?";
            $parameters = [$class, $entity['id']];

            $this->logQuery($logger, $query, $parameters);
            if (!$dryRun) {
                $this->connection->executeStatement($query, $parameters);
            }
        }
    }
}
